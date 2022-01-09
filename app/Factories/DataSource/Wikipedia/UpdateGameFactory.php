<?php

namespace App\Factories\DataSource\Wikipedia;

use App\Models\DataSourceParsed;
use App\Models\Game;
use App\Models\GameImportRuleWikipedia;
use App\Services\DataSources\Wikipedia\UpdateGame;

class UpdateGameFactory
{
    public static function doUpdate(Game $game, DataSourceParsed $dsItem, GameImportRuleWikipedia $gameImportRule = null)
    {
        $serviceUpdateGame = new UpdateGame($game, $dsItem, $gameImportRule);
        $serviceUpdateGame->updateReleaseDateUS();
        $serviceUpdateGame->updateReleaseDateJP();
        // Temporarily stop doing dev/pub updates so we can phase out the old fields on the games table.
        //$serviceUpdateGame->updateDevelopers();
        //$serviceUpdateGame->updatePublishers();
        $game->save();
    }
}