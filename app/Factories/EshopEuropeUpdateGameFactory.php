<?php

namespace App\Factories;

use App\Game;
use App\Services\Eshop\Europe\UpdateGameData;

use App\Services\EshopEuropeGameService;

class EshopEuropeUpdateGameFactory
{
    public static function updateGame(Game $game)
    {
        $serviceUpdateGameData = new UpdateGameData();
        $serviceEshopEuropeGame = new EshopEuropeGameService();

        // GAME CORE DATA
        $gameId = $game->id;
        $gameTitle = $game->title;
        $gameReleaseDate = $game->regionReleaseDate('eu');

        // NB. We can't lazy load this relationship or it borks the game change history table.
        // Best to load it directly from the db.
        //$eshopItem = $game->eshopEuropeGame;

        // Check if we have an eShop record linked to this game
        $fsId = $game->eshop_europe_fs_id;
        if (!$fsId) {
            throw new \Exception('No eShop record linked to game: '.$gameId);
        }
        $eshopItem = $serviceEshopEuropeGame->getByFsId($fsId);
        if (!$eshopItem) {
            throw new \Exception('Cannot locate eShop record linked to game: '.$gameId);
        }

        // Setup
        $serviceUpdateGameData->setEshopItem($eshopItem);
        $serviceUpdateGameData->setGame($game);
        $serviceUpdateGameData->setGameReleaseDate($gameReleaseDate);
        $serviceUpdateGameData->resetLogMessages();

        // Update
        $serviceUpdateGameData->updateNoOfPlayers();
        $serviceUpdateGameData->updatePublisher();
        $serviceUpdateGameData->updatePrice();
        $serviceUpdateGameData->updateReleaseDate();
        $serviceUpdateGameData->updateGenres();

        // ***************************************************** //

        if ($serviceUpdateGameData->hasGameChanged()) {
            $game->save();
        }

        if ($serviceUpdateGameData->hasGameReleaseDateChanged()) {
            $gameReleaseDate = $serviceUpdateGameData->getGameReleaseDate();
            $gameReleaseDate->save();
        }
    }
}