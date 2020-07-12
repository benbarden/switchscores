<?php

namespace App\Factories\DataSource\NintendoCoUk;

use App\DataSourceParsed;
use App\Game;
use App\GameImportRuleEshop;
use App\Services\DataSources\NintendoCoUk\UpdateGame;

class UpdateGameFactory
{
    public static function doUpdate(Game $game, DataSourceParsed $dsItem, GameImportRuleEshop $gameImportRule = null)
    {
        $serviceUpdateGame = new UpdateGame($game, $dsItem, $gameImportRule);
        $serviceUpdateGame->updatePrice();
        $serviceUpdateGame->updateReleaseDate();
        $serviceUpdateGame->updatePlayers();
        $serviceUpdateGame->updateGenres();
        // Temporarily stop doing dev/pub updates so we can phase out the old fields on the games table.
        //$serviceUpdateGame->updatePublishers();
        $game->save();
    }
}