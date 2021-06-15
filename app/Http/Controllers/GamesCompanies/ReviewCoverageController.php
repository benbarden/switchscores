<?php

namespace App\Http\Controllers\GamesCompanies;

use Illuminate\Routing\Controller as Controller;

use App\Domain\Game\Repository as GameRepository;
use App\Domain\ReviewLink\Repository as ReviewLinkRepository;
use App\Domain\Partner\Repository as PartnerRepository;

use App\Traits\SwitchServices;
use App\Traits\AuthUser;

class ReviewCoverageController extends Controller
{
    use SwitchServices;
    use AuthUser;

    protected $repoGame;
    protected $repoReviewLink;
    protected $repoPartner;

    public function __construct(
        GameRepository $repoGame,
        ReviewLinkRepository $repoReviewLink,
        PartnerRepository $repoPartner
    )
    {
        $this->repoGame = $repoGame;
        $this->repoReviewLink = $repoReviewLink;
        $this->repoPartner = $repoPartner;
    }

    public function show($gameId)
    {
        $game = $this->repoGame->find($gameId);
        if (!$game) abort(404);

        $bindings = [];

        $bindings['GameData'] = $game;
        $reviewLinkList = $this->repoReviewLink->byGame($gameId);
        $bindings['ReviewLinkList'] = $reviewLinkList;

        $reviewedPartnerIdList = $reviewLinkList->pluck('partner_id');
        $activeReviewSites = $this->repoPartner->reviewSitesActiveRecent();
        $notReviewedList = $activeReviewSites->whereNotIn('id', $reviewedPartnerIdList)->sortByDesc('last_review_date');
        $bindings['NotReviewedPartnerList'] = $notReviewedList;

        $pageTitle = 'Review coverage';
        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        return view('games-companies.review-coverage.show', $bindings);
    }
}
