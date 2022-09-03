<?php


namespace App\Domain\Game;

use App\Models\Game;
use App\Models\Partner;

class QualityFilter
{
    public function updateGamesByPartner(Partner $partner, $isLowQuality)
    {
        $devGameCount = $partner->developerGames->count();
        $pubGameCount = $partner->publisherGames->count();

        $gameIdList = [];
        if ($devGameCount > 0) {
            foreach ($partner->developerGames as $developerGame) {
                $gameIdList[] = $developerGame->game_id;
            }
        }
        if ($pubGameCount > 0) {
            foreach ($partner->publisherGames as $publisherGame) {
                $gameIdList[] = $publisherGame->game_id;
            }
        }

        if (count($gameIdList) > 0) {
            Game::whereIn('id', $gameIdList)->update(['is_low_quality' => $isLowQuality]);
        }
    }

    public function updateGame(Game $game, Partner $partner)
    {
        $game->is_low_quality = $partner->is_low_quality;
        $game->save();
    }
}