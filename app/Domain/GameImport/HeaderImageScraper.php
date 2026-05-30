<?php

namespace App\Domain\GameImport;

use App\Domain\Game\Repository as GameRepository;
use App\Domain\Scraper\NintendoCoUkPackshot;
use App\Models\Game;
use App\Services\DataSources\NintendoCoUk\Images;

/**
 * Scrapes Nintendo store page to extract and download header image.
 */
class HeaderImageScraper
{
    public function __construct(
        private GameRepository $gameRepository,
    ) {
    }

    /**
     * Scrape the store page for header image URL and download it.
     *
     * @param Game $game The game to update
     * @param string $storeUrl Nintendo store page URL
     * @return bool True if download succeeded
     */
    public function downloadFromStorePage(Game $game, string $storeUrl): bool
    {
        if (empty($storeUrl)) {
            return false;
        }

        try {
            // Scrape the store page
            $scraper = new NintendoCoUkPackshot();
            $scraper->crawlPage($storeUrl);

            $headerUrl = $scraper->getHeaderUrl();

            if (empty($headerUrl)) {
                \Log::warning("No header image found on store page for game {$game->id}: {$storeUrl}");
                return false;
            }

            // Download the header image
            $imageService = new Images($game);
            $filename = $imageService->downloadRemoteHeader($headerUrl, $game->id);

            if ($filename) {
                $game->image_header = $filename;
                $game->save();
                $this->gameRepository->clearCacheCoreData($game->id);
                return true;
            }
        } catch (\Exception $e) {
            // Log error but don't fail the import
            \Log::warning("Failed to download header image for game {$game->id}: " . $e->getMessage());
        }

        return false;
    }
}
