<?php

namespace App\Domain\GameImport;

use App\Domain\Game\Repository as GameRepository;
use App\Models\Game;
use App\Services\DataSources\NintendoCoUk\Images;

/**
 * Downloads square packshot image from a direct URL.
 */
class SquareImageDownloader
{
    public function __construct(
        private GameRepository $gameRepository,
    ) {
    }

    /**
     * Download square image from URL and update the game.
     *
     * @param Game $game The game to update
     * @param string $imageUrl Direct URL to the square image
     * @return bool True if download succeeded
     */
    public function download(Game $game, string $imageUrl): bool
    {
        if (empty($imageUrl)) {
            return false;
        }

        try {
            $imageService = new Images($game);
            $filename = $imageService->downloadRemoteSquare($imageUrl, $game->id);

            if ($filename) {
                $game->image_square = $filename;
                $game->save();
                $this->gameRepository->clearCacheCoreData($game->id);
                return true;
            }
        } catch (\Exception $e) {
            // Log error but don't fail the import
            \Log::warning("Failed to download square image for game {$game->id}: " . $e->getMessage());
        }

        return false;
    }
}
