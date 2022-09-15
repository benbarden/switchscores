<?php


namespace App\Domain\Game;

use App\Models\Game;
use App\Models\GamesCompany;

class QualityFilter
{
    public function updateGamesByPartner(GamesCompany $gamesCompany, $isLowQuality)
    {
        $devGameCount = $gamesCompany->developerGames->count();
        $pubGameCount = $gamesCompany->publisherGames->count();

        $gameIdList = [];
        if ($devGameCount > 0) {
            foreach ($gamesCompany->developerGames as $developerGame) {
                $gameIdList[] = $developerGame->game_id;
            }
        }
        if ($pubGameCount > 0) {
            foreach ($gamesCompany->publisherGames as $publisherGame) {
                $gameIdList[] = $publisherGame->game_id;
            }
        }

        if (count($gameIdList) > 0) {
            Game::whereIn('id', $gameIdList)->update(['is_low_quality' => $isLowQuality]);
        }
    }

    public function updateGame(Game $game, GamesCompany $gamesCompany)
    {
        $game->is_low_quality = $gamesCompany->is_low_quality;
        $game->save();
    }
}