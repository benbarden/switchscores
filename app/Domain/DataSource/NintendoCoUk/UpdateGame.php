<?php

namespace App\Domain\DataSource\NintendoCoUk;

use App\Enums\GameStatus;
use App\Models\Game;
use App\Models\DataSource;
use App\Models\DataSourceParsed;

class UpdateGame
{
    // Minimum number of link IDs required to proceed with updates.
    // Prevents mass de-listing if API data is missing or import fails.
    private const MIN_LINK_IDS_THRESHOLD = 1000;

    public function updateDigitalAvailable(): int
    {
        $linkIds = DataSourceParsed::where('source_id', DataSource::DSID_NINTENDO_CO_UK)->get()->pluck('link_id');

        if ($linkIds->count() < self::MIN_LINK_IDS_THRESHOLD) {
            return 0;
        }

        // Update format_digital (maintains history)
        // Only mark as available if in API data
        Game::whereIn('eshop_europe_fs_id', $linkIds)
            ->update(['format_digital' => Game::FORMAT_AVAILABLE]);

        // For override URL games, only mark available if crawl confirmed 200
        Game::whereNotNull('nintendo_store_url_override')
            ->where('format_digital', Game::FORMAT_DELISTED)
            ->where('last_crawl_status', 200)
            ->update(['format_digital' => Game::FORMAT_AVAILABLE]);

        // Update game_status: only change DELISTED -> ACTIVE (never touch SOFT_DELETED)
        // Only reset if in API data
        Game::whereIn('eshop_europe_fs_id', $linkIds)
            ->where('game_status', GameStatus::DELISTED)
            ->update(['game_status' => GameStatus::ACTIVE]);

        // For override URL games, only reset to active if crawl confirmed 200
        Game::whereNotNull('nintendo_store_url_override')
            ->where('game_status', GameStatus::DELISTED)
            ->where('last_crawl_status', 200)
            ->update(['game_status' => GameStatus::ACTIVE]);

        return $linkIds->count();
    }

    public function updateDigitalDelisted(): int
    {
        $linkIds = DataSourceParsed::where('source_id', DataSource::DSID_NINTENDO_CO_UK)->get()->pluck('link_id');

        if ($linkIds->count() < self::MIN_LINK_IDS_THRESHOLD) {
            return 0;
        }

        // Update format_digital (maintains history)
        Game::whereNull('nintendo_store_url_override')
            ->whereNotIn('eshop_europe_fs_id', $linkIds)
            ->update(['format_digital' => Game::FORMAT_DELISTED]);

        // Update game_status: only change ACTIVE -> DELISTED (never touch SOFT_DELETED)
        Game::whereNull('nintendo_store_url_override')
            ->whereNotIn('eshop_europe_fs_id', $linkIds)
            ->where('game_status', GameStatus::ACTIVE)
            ->update(['game_status' => GameStatus::DELISTED]);

        return $linkIds->count();
    }
}