<?php

namespace App\Http\Controllers;

class GamesController extends BaseController
{
    /**
     * @param $id
     * @param $linkTitle
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
     */
    public function show($id, $linkTitle)
    {
        $bindings = array();

        $gameData = \App\Game::where('id', $id)->get();

        if (!$gameData) {
            abort(404);
        }

        $gameData = $gameData->first();

        if ($gameData->link_title != $linkTitle) {
            $redirUrl = sprintf('/games/%s/%s', $id, $gameData->link_title);
            return redirect($redirUrl, 301);
        }

        // Get chart rankings for this game
        $gameRanking = \App\ChartsRanking::where('game_id', $gameData->id)
            ->orderBy('chart_date', 'desc')
            ->get();

        // Get reviews
        $gameReviews = \App\ReviewLink::where('game_id', $gameData->id)
            ->orderBy('site_id', 'asc')
            ->get();

        $bindings['TopTitle'] = $gameData->title;
        $bindings['GameData'] = $gameData;
        $bindings['GameRanking'] = $gameRanking;
        $bindings['GameReviews'] = $gameReviews;

        return view('games.show', $bindings);
    }

    /**
     * This is for redirecting old links. Do not use for new links.
     * @param integer $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function showId($id)
    {
        $gameData = \App\Game::where('id', $id)->get();

        if (!$gameData) {
            abort(404);
        }

        $gameData = $gameData->first();

        $redirUrl = sprintf('/games/%s/%s', $id, $gameData->link_title);
        return redirect($redirUrl, 301);
    }
}
