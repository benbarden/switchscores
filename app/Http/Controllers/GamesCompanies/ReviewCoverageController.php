<?php

namespace App\Http\Controllers\GamesCompanies;

use Illuminate\Routing\Controller as Controller;

use App\Domain\Game\Repository as GameRepository;
use App\Domain\ReviewLink\Repository as ReviewLinkRepository;
use App\Domain\ReviewSite\Repository as ReviewSiteRepository;

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
        $game = $this->repoGame->find($gameId);
        if (!$game) abort(404);

        $bindings = [];

        $bindings['GameData'] = $game;
        $reviewLinkList = $this->repoReviewLink->byGame($gameId)->sortByDesc('review_date');
        $bindings['ReviewLinkList'] = $reviewLinkList;

        $reviewedPartnerIdList = $reviewLinkList->pluck('site_id');
        $activeReviewSites = $this->repoReviewSite->getActive();
        $notReviewedList = $activeReviewSites->whereNotIn('id', $reviewedPartnerIdList)->sortByDesc('last_review_date');
        $bindings['NotReviewedPartnerList'] = $notReviewedList;

        $bindings['ReviewSitesWithContactDetails'] = $this->repoReviewSite->getActiveWithContactDetails();

        $pageTitle = 'Review coverage';
        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        return view('games-companies.review-coverage.show', $bindings);
    }
}
