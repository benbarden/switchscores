<?php

namespace App\Http\Controllers\Members\GamesCompanies;

use App\Domain\Game\Repository as GameRepository;
use App\Domain\ReviewLink\Repository as ReviewLinkRepository;
use App\Domain\ReviewSite\Repository as ReviewSiteRepository;
use Illuminate\Routing\Controller as Controller;

class ReviewCoverageController extends Controller
{

    public function __construct(
        private GameRepository $repoGame,
        private ReviewLinkRepository $repoReviewLink,
        private ReviewSiteRepository $repoReviewSite
    )
    {
    }

    public function show($gameId)
    {
        $pageTitle = 'Review coverage';
        $breadcrumbs = resolve('View/Breadcrumbs/Member')->gamesCompaniesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Member')->setBreadcrumbs($breadcrumbs)->generateMember($pageTitle);

        $game = $this->repoGame->find($gameId);
        if (!$game) abort(404);

        $bindings['GameData'] = $game;
        $reviewLinkList = $this->repoReviewLink->byGame($gameId)->sortByDesc('review_date');
        $bindings['ReviewLinkList'] = $reviewLinkList;

        $reviewedPartnerIdList = $reviewLinkList->pluck('site_id');
        $activeReviewSites = $this->repoReviewSite->getActive();
        $notReviewedList = $activeReviewSites->whereNotIn('id', $reviewedPartnerIdList)->sortByDesc('last_review_date');
        $bindings['NotReviewedPartnerList'] = $notReviewedList;

        $bindings['ReviewSitesWithContactDetails'] = $this->repoReviewSite->getActiveWithContactDetails();

        return view('members.games-companies.review-coverage.show', $bindings);
    }
}
