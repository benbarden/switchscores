<?php

namespace App\Http\Controllers\GamesCompanies;

use Illuminate\Routing\Controller as Controller;

use App\Domain\Game\Repository as GameRepository;
use App\Domain\ReviewLink\Repository as ReviewLinkRepository;
use App\Domain\ReviewSite\Repository as ReviewSiteRepository;

use App\Traits\SwitchServices;
use App\Traits\AuthUser;

class ReviewCoverageController extends Controller
{
    use SwitchServices;
    use AuthUser;

    protected $repoGame;
    protected $repoReviewLink;
    protected $repoReviewSite;

    public function __construct(
        GameRepository $repoGame,
        ReviewLinkRepository $repoReviewLink,
        ReviewSiteRepository $repoReviewSite
    )
    {
        $this->repoGame = $repoGame;
        $this->repoReviewLink = $repoReviewLink;
        $this->repoReviewSite = $repoReviewSite;
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
