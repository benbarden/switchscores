<?php

namespace App\Http\Controllers\PublicSite;

use App\Domain\QuickReview\Repository as QuickReviewRepository;
use App\Domain\User\Repository as UserRepository;
use App\Domain\UserGamesCollection\Stats as UserGamesCollectionStats;

use App\Traits\SwitchServices;

use Illuminate\Routing\Controller as Controller;

class CommunityController extends Controller
{
    use SwitchServices;

    public function __construct(
        private QuickReviewRepository $repoQuickReview,
        private UserRepository $repoUser,
        private UserGamesCollectionStats $statsUserGamesCollection
    )
    {
    }

    public function landing()
    {
        $bindings = [];

        $bindings['QuickReviews'] = $this->repoQuickReview->getLatestActive(5);
        $bindings['HallOfFame'] = $this->repoUser->getMostPoints(5);

        // Stats
        $bindings['NewestUser'] = $this->repoUser->getNewest();
        $bindings['CollectionCount'] = $this->statsUserGamesCollection->countAllCollections();


        $bindings['TopTitle'] = 'Community';
        $bindings['PageTitle'] = 'Community';

        return view('public.community.landing', $bindings);
    }
}
