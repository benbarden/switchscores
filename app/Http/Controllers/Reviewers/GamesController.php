<?php

namespace App\Http\Controllers\Reviewers;

use Illuminate\Routing\Controller as Controller;

use App\Domain\FeaturedGame\Repository as FeaturedGameRepository;
use App\Domain\GameStats\Repository as GameStatsRepository;

use App\Traits\SwitchServices;

class GamesController extends Controller
{
    use SwitchServices;

    public function __construct(
        private FeaturedGameRepository $repoFeaturedGames,
        private GameStatsRepository $repoGameStats
    )
    {
    }

    public function show($gameId)
    {
        $bindings = [];

        $game = $this->getServiceGame()->find($gameId);
        if (!$game) abort(404);

        $pageTitle = 'Game detail: '.$game->title;

        $bindings['GameData'] = $game;
        $bindings['RankMaximum'] = $this->repoGameStats->totalRanked();
        $bindings['GameTags'] = $this->getServiceGameTag()->getByGame($gameId);

        $currentUser = resolve('User/Repository')->currentUser();
        $partnerId = $currentUser->partner_id;

        // Nintendo.co.uk API data
        $bindings['DataSourceNintendoCoUk'] = $this->getServiceDataSourceParsed()->getSourceNintendoCoUkForGame($gameId);

        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        return view('reviewers.games.show', $bindings);
    }
}
