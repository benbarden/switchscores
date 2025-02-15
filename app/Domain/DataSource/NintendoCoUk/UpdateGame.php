<?php

namespace App\Domain\DataSource\NintendoCoUk;

use App\Models\Game;
use App\Models\DataSource;
use App\Models\DataSourceParsed;

class UpdateGame
{
    public function updateDigitalAvailable()
    {
        $linkIds = DataSourceParsed::where('source_id', DataSource::DSID_NINTENDO_CO_UK)->get()->pluck('link_id');

        Game::whereIn('eshop_europe_fs_id', $linkIds)
            ->update(['format_digital' => Game::FORMAT_AVAILABLE]);

        Game::whereNotNull('nintendo_store_url_override')
            ->where('format_digital', Game::FORMAT_DELISTED)
            ->update(['format_digital' => Game::FORMAT_AVAILABLE]);

    }

    public function updateDigitalDelisted()
    {
        $linkIds = DataSourceParsed::where('source_id', DataSource::DSID_NINTENDO_CO_UK)->get()->pluck('link_id');

        Game::whereNull('nintendo_store_url_override')
            ->whereNotIn('eshop_europe_fs_id', $linkIds)
            ->update(['format_digital' => Game::FORMAT_DELISTED]);
    }

}