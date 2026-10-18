<?php

namespace App\Factories\DataSource\NintendoCoUk;

use App\Domain\DataSource\NintendoCoUk\GamePriceDecision;
use App\Models\DataSourceParsed;
use App\Models\Game;
use App\Models\GameImportRuleEshop;
use App\Services\DataSources\NintendoCoUk\UpdateGame;

class UpdateGameFactory
{
    public static function doUpdate(Game $game, DataSourceParsed $dsItem, GameImportRuleEshop $gameImportRule = null)
    {
        // Null for Switch 1, so its price comes from the search payload as before (#183)
        $apiPriceDecision = app(GamePriceDecision::class)->forGame($game);

        $serviceUpdateGame = new UpdateGame($game, $dsItem, $gameImportRule, $apiPriceDecision);
        $serviceUpdateGame->updatePrice();
        $serviceUpdateGame->updateReleaseDate();
        $serviceUpdateGame->updatePlayers();
        $serviceUpdateGame->updatePhysicalVersion();
        $serviceUpdateGame->updateDLC();
        $serviceUpdateGame->updateDemo();
        // Temporarily stop doing dev/pub updates so we can phase out the old fields on the games table.
        //$serviceUpdateGame->updatePublishers();
        if ($game->isDirty()) {
            $game->save();
        }
    }
}