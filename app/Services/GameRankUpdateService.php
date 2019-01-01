<?php


namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\GameRankUpdate;


class GameRankUpdateService
{
    public function create($gameId, $rankOld, $rankNew, $ratingAvg)
    {
        $movement = null;
        if ($rankOld) {
            $movement = $rankOld - $rankNew;
        }

        GameRankUpdate::create([
            'game_id' => $gameId,
            'rank_old' => $rankOld,
            'rank_new' => $rankNew,
            'movement' => $movement,
            'rating_avg' => $ratingAvg,
        ]);
    }

    public function delete($id)
    {
        GameRankUpdate::where('id', $id)->delete();
    }

    public function find($id)
    {
        return GameRankUpdate::find($id);
    }

    // ********************************************************** //

    public function getByGame($gameId)
    {
        return GameRankUpdate::where('game_id', $gameId)->get();
    }

    public function getRecent($limit = 30)
    {
        return GameRankUpdate::orderBy('created_at', 'desc')->get();
    }
}