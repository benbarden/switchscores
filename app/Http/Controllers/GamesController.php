<?php

namespace App\Http\Controllers;

class GamesController extends BaseController
{
    public function listReleased()
    {
        $bindings = array();

        $gamesList = $this->serviceGame->getListReleased();

        $bindings['GamesList'] = $gamesList;

        $bindings['TopTitle'] = 'Nintendo Switch released games';
        return view('games.list.releasedGames', $bindings);
    }

    public function listUpcoming()
    {
        $bindings = array();

        $gamesList = $this->serviceGame->getListUpcoming();

        $bindings['GamesList'] = $gamesList;

        $bindings['TopTitle'] = 'Nintendo Switch upcoming games';
        return view('games.list.upcomingGames', $bindings);
    }

    public function listTopRated()
    {
        $bindings = array();

        $gamesList = $this->serviceGame->getListTopRated();

        $bindings['GamesList'] = $gamesList;

        $bindings['TopTitle'] = 'Nintendo Switch Top Rated games';
        return view('games.list.topRated', $bindings);
    }

    public function listReviewsNeeded()
    {
        $bindings = array();

        $gamesList = $this->serviceGame->getListReviewsNeeded();

        $bindings['GamesList'] = $gamesList;

        $bindings['TopTitle'] = 'Nintendo Switch - Games needing more reviews';
        return view('games.list.reviewsNeeded', $bindings);
    }

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
