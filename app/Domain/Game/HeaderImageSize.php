<?php

namespace App\Domain\Game;

use App\Models\Game;
use App\Models\GameScrapedData;
use App\Services\Game\Images as GameImages;

/**
 * "How big is the header packshot we currently hold for this game?"
 *
 * The crawl commands answer "has Nintendo changed the header image?" by comparing the remote
 * file size against the size of the copy we already have. That question has to be answered for
 * whichever location the game is actually stored in.
 *
 * It used to be answered from public/img/ps-header/{games.image_header} + file_exists() alone.
 * That is a legacy-only signal, and PackshotWriter deliberately leaves games.image_header null
 * when it writes to object storage - so a game stored in Spaces reported "no local copy"
 * forever, never matched the remote size, and was re-downloaded on EVERY crawl. Same shape as
 * the DownloadPackshotHelper::isEligibleForDownload() trap fixed on 2026-07-20, reached by a
 * different route: silent, no error, just a permanent re-scrape.
 *
 * For games in object storage the size comes from game_scraped_data.header_image_size, which the
 * crawl commands write on every check and after every download - so it tracks the current file
 * without a HEAD request against Spaces in a hot loop (100 games/night).
 *
 * Returns null when we hold nothing, which correctly reads as "differs from remote" and triggers
 * a download. The resolver check matters: a stored size with no actual image must NOT suppress
 * the download that would fetch it.
 */
class HeaderImageSize
{
    public function __construct(private ImageResolver $resolver)
    {
    }

    /**
     * Size in bytes of the header packshot currently held, or null if there isn't one.
     */
    public function current(Game $game): ?int
    {
        // Legacy games: the file on disk is authoritative. Kept first so that a game which has
        // both a legacy file and a game_images row is measured, not merely trusted.
        if ($game->image_header) {
            $localPath = public_path() . GameImages::PATH_IMAGE_HEADER . $game->image_header;

            if (file_exists($localPath)) {
                return filesize($localPath);
            }
        }

        // Object storage: trust the recorded size only if a header actually resolves. Without
        // this guard a stale game_scraped_data row could match the remote size for a game whose
        // image was never stored, and suppress the download forever.
        if (!$this->resolver->url($game, ImageResolver::TYPE_HEADER)) {
            return null;
        }

        $scrapedData = GameScrapedData::where('game_id', $game->id)->first();

        if (!$scrapedData || $scrapedData->header_image_size === null) {
            return null;
        }

        return (int) $scrapedData->header_image_size;
    }
}
