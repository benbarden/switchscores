<?php

namespace App\Factories\DataSource\Wikipedia;

use App\DataSourceParsed;
use App\Game;
use App\GameImportRuleWikipedia;
use App\Services\DataSources\Wikipedia\UpdateGame;

class UpdateGameFactory
{
    public static function doUpdate(Game $game, DataSourceParsed $dsItem, GameImportRuleWikipedia $gameImportRule = null)
    {
        $serviceUpdateGame = new UpdateGame($game, $dsItem, $gameImportRule);
        $serviceUpdateGame->updateDevelopers();
        $serviceUpdateGame->updatePublishers();
        $serviceUpdateGame->updateReleaseDateUS();
        $serviceUpdateGame->updateReleaseDateJP();
        $game->save();
    }
}