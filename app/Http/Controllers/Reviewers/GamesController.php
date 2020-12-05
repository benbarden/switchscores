<?php

namespace App\Http\Controllers\Reviewers;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;
use App\Traits\AuthUser;

class GamesController extends Controller
{
    use SwitchServices;
    use AuthUser;

    public function show($gameId)
    {
        $bindings = [];

        $game = $this->getServiceGame()->find($gameId);
        if (!$game) abort(404);

        $pageTitle = 'Game detail: '.$game->title;

        $bindings['GameData'] = $game;
        $bindings['RankMaximum'] = $this->getServiceGame()->countRanked();

        $authUser = $this->getValidUser($this->getServiceUser());
        $partnerId = $authUser->partner_id;

        // Nintendo.co.uk API data
        $bindings['DataSourceNintendoCoUk'] = $this->getServiceDataSourceParsed()->getSourceNintendoCoUkForGame($gameId);
        $bindings['DataSourceWikipedia'] = $this->getServiceDataSourceParsed()->getSourceWikipediaForGame($gameId);

        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        return view('reviewers.games.show', $bindings);
    }
}
