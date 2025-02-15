<?php

namespace App\Domain\DataSource\NintendoCoUk;

use App\Models\Game;
use App\Models\DataSource;

use Illuminate\Support\Facades\DB;

class UpdateGame
{
    public function updateDigitalAvailable()
    {
        $sourceId = DataSource::DSID_NINTENDO_CO_UK;
        DB::update('
            UPDATE games
            SET format_digital = ?
            WHERE eshop_europe_fs_id IN (
                SELECT link_id FROM data_source_parsed WHERE source_id = ?
            );
        ', [Game::FORMAT_AVAILABLE, $sourceId]);

        DB::update('
            UPDATE games
            SET format_digital = ?
            WHERE nintendo_store_url_override IS NOT NULL
            AND format_digital = ?
        ', [Game::FORMAT_AVAILABLE, Game::FORMAT_DELISTED]);
    }

    public function updateDigitalDelisted()
    {
        $sourceId = DataSource::DSID_NINTENDO_CO_UK;
        DB::update('
            UPDATE games
            SET format_digital = ?
            WHERE nintendo_store_url_override IS NULL
            AND eshop_europe_fs_id NOT IN (
                SELECT link_id FROM data_source_parsed WHERE source_id = ?
            );
        ', [Game::FORMAT_DELISTED, $sourceId]);
    }

}