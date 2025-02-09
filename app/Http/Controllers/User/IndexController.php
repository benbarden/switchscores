<?php

namespace App\Http\Controllers\User;

use Illuminate\Routing\Controller as Controller;

use App\Domain\UserGamesCollection\CollectionStatsRepository;
use App\Domain\FeaturedGame\Repository as FeaturedGameRepository;
use App\Domain\Game\Repository as GameRepository;

class IndexController extends Controller
{
    public function __construct(
        private CollectionStatsRepository $repoCollectionStats,
        private FeaturedGameRepository $repoFeaturedGame,
        private GameRepository $repoGame,
    )
    {
    }

    public function show()
    {
        $pageTitle = 'Members dashboard';
        $breadcrumbs = resolve('View/Breadcrumbs/Member')->topLevelPage($pageTitle);
        $bindings = resolve('View/Bindings/Member')->setBreadcrumbs($breadcrumbs)->generateMember($pageTitle);

        $siteRole = 'member'; // default

        $currentUser = resolve('User/Repository')->currentUser();
        $userId = $currentUser->id;

        $bindings['SiteRole'] = $siteRole;
        $bindings['UserData'] = $currentUser;
        $bindings['TotalGames'] = $this->repoCollectionStats->userTotalGames($userId);
        $bindings['TotalHours'] = $this->repoCollectionStats->userTotalHours($userId);

        // Featured game
        $featuredGame = $this->repoFeaturedGame->getLatest();
        $game = $this->repoGame->find($featuredGame->game_id);
        if ($game) {
            $bindings['FeaturedGame'] = $featuredGame;
            $bindings['FeaturedGameData'][] = $game;
        }

        return view('user.index', $bindings);
    }
}
