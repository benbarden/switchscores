<?php

namespace App\Http\Controllers\User;

use Illuminate\Routing\Controller as Controller;

use App\Domain\UserGamesCollection\CollectionStatsRepository;
use App\Domain\UserGamesCollection\DbQueries as CollectionDbQueries;
use App\Domain\FeaturedGame\Repository as FeaturedGameRepository;
use App\Domain\Game\Repository as GameRepository;
use App\Domain\Unranked\Repository as UnrankedRepository;

class IndexController extends Controller
{
    public function __construct(
        private CollectionStatsRepository $repoCollectionStats,
        private CollectionDbQueries $dbCollection,
        private FeaturedGameRepository $repoFeaturedGame,
        private GameRepository $repoGame,
        private UnrankedRepository $repoUnranked,
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

        // Games to review
        $nextToReviewInCollectionGameId = $this->dbCollection->nextToReviewInCollection($userId);
        if ($nextToReviewInCollectionGameId) {
            $nextToReviewFromCollection = $this->repoGame->find($nextToReviewInCollectionGameId[0]->id);
            $bindings['NextToReviewFromCollection'][] = $nextToReviewFromCollection;
        }
        $nextToReviewFromUnranked = $this->repoUnranked->getForMemberDashboard();
        $bindings['NextToReviewFromUnranked'] = $nextToReviewFromUnranked;

        return view('user.index', $bindings);
    }
}
