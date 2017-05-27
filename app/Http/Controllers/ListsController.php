<?php

namespace App\Http\Controllers;

class ListsController extends BaseController
{
    public function releasedGames()
    {
        $bindings = array();

        $gamesList = \App\Game::where('upcoming', 0)
            ->orderBy('release_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $bindings['GamesList'] = $gamesList;

        $bindings['TopTitle'] = 'Nintendo Switch released games | Nintendo Switch charts and stats';
        return view('lists.releasedGames', $bindings);
    }

    public function upcomingGames()
    {
        $bindings = array();

        $gamesList = \App\Game::where('upcoming', 1)
            ->orderBy('upcoming_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $bindings['GamesList'] = $gamesList;

        $bindings['TopTitle'] = 'Nintendo Switch upcoming games | Nintendo Switch charts and stats';
        return view('lists.upcomingGames', $bindings);
    }
}
