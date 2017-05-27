<?php

namespace App\Http\Controllers;

class GamesController extends BaseController
{
    public function show($id)
    {
        $bindings = array();

        $gameData = \App\Game::where('id', $id)->get();

        if (!$gameData) {
            abort(404);
        }

        $gameData = $gameData->first();

        // Get chart rankings for this game
        $gameRanking = \App\ChartsRanking::where('game_id', $gameData->id)
            ->orderBy('chart_date', 'desc')
            ->get();

        $bindings['TopTitle'] = $gameData->title.' | Nintendo Switch charts and stats';
        $bindings['GameData'] = $gameData;
        $bindings['GameRanking'] = $gameRanking;

        return view('games.show', $bindings);
    }
}
