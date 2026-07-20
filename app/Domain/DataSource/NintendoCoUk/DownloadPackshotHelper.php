<?php

namespace App\Domain\DataSource\NintendoCoUk;

use App\Domain\Game\ImageResolver;
use App\Domain\GameLists\Repository as RepoGameLists;
use App\Domain\Scraper\NintendoCoUkPackshot;
use App\Services\DataSources\NintendoCoUk\Images;
use App\Models\Game;
use App\Models\GameImage;

class DownloadPackshotHelper
{
    private $gameList;

    private $logger;

    public function __construct(
        private RepoGameLists $repoGameLists
    )
    {
    }

    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    public function downloadAllWithDataSourceId()
    {
        $this->gameList = $this->repoGameLists->anyWithNintendoCoUkId();

        if ($this->logger) {
            $this->logger->info('Found ' . count($this->gameList) . ' item(s)');
        }

        foreach ($this->gameList as $gameItem) {

            $this->downloadForGame($gameItem);

        }
    }

    public function downloadAllWithOverrideUrl()
    {
        $this->gameList = $this->repoGameLists->anyWithStoreOverride();

        if ($this->logger) {
            $this->logger->info('Found ' . count($this->gameList) . ' item(s)');
        }

        foreach ($this->gameList as $gameItem) {

            $this->downloadForGame($gameItem);

        }
    }

    public function downloadForGame(Game $game)
    {
        $itemTitle = $game->title;
        $gameId = $game->id;

        if ($this->isEligibleForDownload($game)) {
            if ($this->logger) {
                $this->logger->info('');
                $this->logger->info('Game is eligible for image download: '.$itemTitle.' ['.$gameId.']');
            }
        } else {
            return;
        }

        $packshotBuilder = new PackshotUrlBuilder();

        if ($this->hasValidDataSourceItem($game)) {

            // use DS item data
            $dsItem = $game->dspNintendoCoUk()->first();
            /*
            if ($this->logger) {
                $this->logger->info('Downloading using data source item...');
            }

            $dsItem = $game->dspNintendoCoUk()->first();
            $downloadByDataSource = new DownloadByDataSource($game, $dsItem);
            $downloadByDataSource->download();
            */

            $storeUrl = 'https://www.nintendo.com'.$dsItem->url;

        } else {

            $dsItem = null;
            $storeUrl = $game->nintendo_store_url_override;

        }

        if ($storeUrl) {

            // use scraper
            if ($this->logger) {
                $this->logger->info('Downloading using scraper...');
            }

            $scraper = new NintendoCoUkPackshot();
            //$storeUrl = $game->nintendo_store_url_override;
            $squareUrlOverride = $game->packshot_square_url_override;

            try {
                $scraper->crawlPage($storeUrl);
                if ($dsItem) {
                    $squareUrl = $dsItem->image_square;
                } else {
                    $squareUrl = $scraper->getSquareUrl();
                }
                $headerUrl = $scraper->getHeaderUrl();
                // If we have an override, the generated URL probably errored.
                // In which case, let's just use that straight away.
                if ($squareUrlOverride) {
                    if ($this->logger) {
                        $this->logger->info('Found packshot_square_url_override');
                    }
                    $squareUrl = $squareUrlOverride;
                }
                // Fallback for missing square images
                // We want to do this AFTER the square URL override, to avoid regex failures
                if ($headerUrl && !$squareUrl) {
                    $squareUrl = $packshotBuilder->getSquareUrl($headerUrl);
                }
                // Download away!
                if ($squareUrl || $headerUrl) {
                    //DownloadImageFactory::downloadFromStoreUrl($game, $squareUrl, $headerUrl, $this->logger);
                    $downloadByOverrideUrl = app(DownloadByOverrideUrl::class); // will inject dependencies
                    $downloadByOverrideUrl->setGame($game);
                    $downloadByOverrideUrl->download($squareUrl, $headerUrl);
                }
            } catch (\Exception $e) {
                if ($this->logger) {
                    $this->logger->error($e->getMessage());
                }
            }
        }
    }

    public function isEligibleForDownload(Game $game)
    {
        $isEligible = false;

        $hasValidDSItem = false;
        $hasBrokenDSItem = false;
        $hasStoreOverride = false;

        if ($this->hasValidDataSourceItem($game)) {
            $dsItem = $game->dspNintendoCoUk()->first();
            $hasValidDSItem = true;
        } elseif ($game->eshop_europe_fs_id) {
            // Broken link, but the images may still exist
            $hasBrokenDSItem = true;
        } elseif ($game->nintendo_store_url_override) {
            $hasStoreOverride = true;
        }

        if (!$hasValidDSItem && !$hasStoreOverride) {
            //$this->logger->info('No viable method to download images, so game is not eligible');
            $isEligible = false;
            return $isEligible;
        }

        // "Does this game already have its packshots?" is asked through ImageResolver, which
        // answers for whichever location the game is actually stored in.
        //
        // This used to read games.image_* and then file_exists() under public/img. Both are
        // legacy-only signals: a game whose packshots live in object storage has null columns
        // and no local file, so it looked permanently eligible. Every run would re-scrape
        // Nintendo and re-upload the same images, silently and forever, from the moment
        // PACKSHOTS_DEFAULT_LOCATION was flipped to `spaces`.
        //
        // The resolver returns '' when nothing resolves in either location, which is exactly
        // the question - and it keeps the legacy existence check for legacy games, because
        // legacyUrl() only returns a URL when the column is populated.
        $resolver = app(ImageResolver::class);

        if (!$resolver->url($game, ImageResolver::TYPE_SQUARE)) {
            //$this->logger->info('Eligible for image download: no square packshot resolves');
            $isEligible = true;
            return $isEligible;
        }

        if (!$resolver->url($game, ImageResolver::TYPE_HEADER)) {
            //$this->logger->info('Eligible for image download: no header packshot resolves');
            $isEligible = true;
            return $isEligible;
        }

        // Legacy games additionally need the file to actually be on disk - the column can name
        // a file that isn't there. Games on object storage are trusted from the game_images row;
        // a HEAD per game per run would be a remote call in a hot loop.
        if (!$game->images || $game->images->location !== GameImage::LOCATION_SPACES) {
            $gameImages = new Images($game);

            $imgSquareFullPath = $gameImages->generateCurrentPathSquare($game);
            if (!file_exists($imgSquareFullPath)) {
                //$this->logger->info('Eligible for download: square image not found on file system ['.$imgSquareFullPath.']');
                $isEligible = true;
                return $isEligible;
            }

            $imgHeaderFullPath = $gameImages->generateCurrentPathHeader($game);
            if (!file_exists($imgHeaderFullPath)) {
                //$this->logger->info('Eligible for download: header image not found on file system ['.$imgHeaderFullPath.']');
                $isEligible = true;
                return $isEligible;
            }
        }

        return $isEligible;
    }

    public function hasValidDataSourceItem(Game $game)
    {
        return $game->dspNintendoCoUk()->first() != null;
    }
}