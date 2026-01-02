<?php

namespace App\Http\Controllers\PublicSite;

use Illuminate\Routing\Controller as Controller;

use App\Domain\View\Breadcrumbs\PublicBreadcrumbs;
use App\Domain\View\PageBuilders\PublicPageBuilder;

use App\Domain\QuickReview\Repository as QuickReviewRepository;
use App\Domain\User\Repository as UserRepository;
use App\Domain\UserGamesCollection\Stats as UserGamesCollectionStats;

class CommunityController extends Controller
{
    public function __construct(
        private PublicPageBuilder $pageBuilder,
        private QuickReviewRepository $repoQuickReview,
        private UserRepository $repoUser,
        private UserGamesCollectionStats $statsUserGamesCollection
    )
    {
    }

    public function landing()
    {
        $pageTitle = 'Community';
        $bindings = $this->pageBuilder->build($pageTitle, PublicBreadcrumbs::topLevel($pageTitle))->bindings;

        $bindings['QuickReviews'] = $this->repoQuickReview->getLatestActive(5);
        $bindings['HallOfFame'] = $this->repoUser->getMostPoints(5);

        // Stats
        $bindings['NewestUser'] = $this->repoUser->getNewest();
        $bindings['CollectionCount'] = $this->statsUserGamesCollection->countAllCollections();

        return view('public.community.landing', $bindings);
    }
}
