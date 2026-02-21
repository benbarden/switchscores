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
        Game::whereIn('eshop_europe_fs_id', $linkIds)
            ->update(['format_digital' => Game::FORMAT_AVAILABLE]);

        Game::whereNotNull('nintendo_store_url_override')
            ->where('format_digital', Game::FORMAT_DELISTED)
            ->update(['format_digital' => Game::FORMAT_AVAILABLE]);

        // Update game_status: only change DELISTED -> ACTIVE (never touch SOFT_DELETED)
        Game::whereIn('eshop_europe_fs_id', $linkIds)
            ->where('game_status', GameStatus::DELISTED)
            ->update(['game_status' => GameStatus::ACTIVE]);

        Game::whereNotNull('nintendo_store_url_override')
            ->where('game_status', GameStatus::DELISTED)
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