<?php

namespace App\Http\Controllers\Reviewers;

use Illuminate\Routing\Controller as Controller;

use App\Domain\Game\Repository as GameRepository;
use App\Domain\GameStats\Repository as GameStatsRepository;
use App\Domain\DataSourceParsed\Repository as DataSourceParsedRepository;
use App\Domain\GameTag\Repository as GameTagRepository;

class GamesController extends Controller
{
    public function __construct(
        private GameRepository $repoGame,
        private GameStatsRepository $repoGameStats,
        private DataSourceParsedRepository $repoDataSourceParsed,
        private GameTagRepository $repoGameTag
    )
    {
    }

    public function show($gameId)
    {
        $game = $this->repoGame->find($gameId);
        if (!$game) abort(404);

        $pageTitle = 'Game detail: '.$game->title;
        $breadcrumbs = resolve('View/Breadcrumbs/Member')->reviewersSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Member')->setBreadcrumbs($breadcrumbs)->generateMember($pageTitle);

        $bindings['GameData'] = $game;
        $bindings['RankMaximum'] = $this->repoGameStats->totalRanked();
        $bindings['GameTags'] = $this->repoGameTag->getGameTags($gameId);

        $currentUser = resolve('User/Repository')->currentUser();
        $partnerId = $currentUser->partner_id;

        // Nintendo.co.uk API data
        $bindings['DataSourceNintendoCoUk'] = $this->repoDataSourceParsed->getSourceNintendoCoUkForGame($gameId);

        return view('reviewers.games.show', $bindings);
    }
}
