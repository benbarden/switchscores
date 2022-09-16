<?php

namespace App\Http\Controllers\Staff\Reviews;

use Illuminate\Routing\Controller as Controller;

use App\Domain\FeaturedGame\Repository as FeaturedGameRepository;
use App\Domain\GameStats\Repository as GameStatsRepository;
use App\Domain\ReviewDraft\Repository as ReviewDraftRepository;
use App\Domain\ReviewDraft\Stats as ReviewDraftStats;

use App\Models\QuickReview;

use App\Traits\SwitchServices;

class DashboardController extends Controller
{
    use SwitchServices;

    protected $repoFeaturedGames;
    protected $repoGameStats;
    protected $repoReviewDraft;
    protected $statsReviewDraft;

    public function __construct(
        FeaturedGameRepository $featuredGames,
        GameStatsRepository $repoGameStats,
        ReviewDraftRepository $repoReviewDraft,
        ReviewDraftStats $statsReviewDraft
    )
    {
        $this->repoFeaturedGames = $featuredGames;
        $this->repoGameStats = $repoGameStats;
        $this->repoReviewDraft = $repoReviewDraft;
        $this->statsReviewDraft = $statsReviewDraft;
    }

    public function show()
    {
        $pageTitle = 'Reviews dashboard';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->topLevelPage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $serviceQuickReview = $this->getServiceQuickReview();
        $serviceReviewLinks = $this->getServiceReviewLink();
        $serviceTopRated = $this->getServiceTopRated();

        // Action lists
        $bindings['ReviewDraftUnprocessedCount'] = $this->repoReviewDraft->countUnprocessed();
        $pendingQuickReview = $serviceQuickReview->getByStatus(QuickReview::STATUS_PENDING);
        $bindings['PendingQuickReviewCount'] = count($pendingQuickReview);

        // Stats
        $bindings['ReviewLinkCount'] = $serviceReviewLinks->countActive();
        $bindings['RankedGameCount'] = $this->repoGameStats->totalRanked();
        $bindings['UnrankedGameCount'] = $serviceTopRated->getUnrankedCount();

        // Unranked breakdown
        $bindings['UnrankedReviews2'] = $this->getServiceTopRated()->getUnrankedCountByReviewCount(2);
        $bindings['UnrankedReviews1'] = $this->getServiceTopRated()->getUnrankedCountByReviewCount(1);
        $bindings['UnrankedReviews0'] = $this->getServiceTopRated()->getUnrankedCountByReviewCount(0);
        $bindings['UnrankedYear2022'] = $this->getServiceTopRated()->getUnrankedCountByReleaseYear(2022);
        $bindings['UnrankedYear2021'] = $this->getServiceTopRated()->getUnrankedCountByReleaseYear(2021);
        $bindings['UnrankedYear2020'] = $this->getServiceTopRated()->getUnrankedCountByReleaseYear(2020);
        $bindings['UnrankedYear2019'] = $this->getServiceTopRated()->getUnrankedCountByReleaseYear(2019);
        $bindings['UnrankedYear2018'] = $this->getServiceTopRated()->getUnrankedCountByReleaseYear(2018);
        $bindings['UnrankedYear2017'] = $this->getServiceTopRated()->getUnrankedCountByReleaseYear(2017);

        $bindings['ProcessStatusStats'] = $this->statsReviewDraft->getProcessStatusStats();

        return view('staff.reviews.dashboard', $bindings);
    }
}
