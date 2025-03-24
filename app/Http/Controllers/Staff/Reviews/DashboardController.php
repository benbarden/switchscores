<?php

namespace App\Http\Controllers\Staff\Reviews;

use Illuminate\Routing\Controller as Controller;

use App\Domain\QuickReview\Repository as QuickReviewRepository;
use App\Domain\FeaturedGame\Repository as FeaturedGameRepository;
use App\Domain\GameStats\Repository as GameStatsRepository;
use App\Domain\ReviewDraft\Repository as ReviewDraftRepository;
use App\Domain\ReviewDraft\Stats as ReviewDraftStats;
use App\Domain\Unranked\Repository as UnrankedRepository;
use App\Domain\ReviewLink\Stats as ReviewLinkStats;

use App\Models\QuickReview;

use App\Traits\SwitchServices;

class DashboardController extends Controller
{
    use SwitchServices;

    public function __construct(
        private FeaturedGameRepository $repoFeaturedGames,
        private GameStatsRepository $repoGameStats,
        private ReviewDraftRepository $repoReviewDraft,
        private QuickReviewRepository $repoQuickReview,
        private ReviewDraftStats $statsReviewDraft,
        private UnrankedRepository $repoUnranked,
        private ReviewLinkStats $statsReviewLink
    )
    {
    }

    public function show()
    {
        $pageTitle = 'Reviews dashboard';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->topLevelPage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $allowedYears = resolve('Domain\GameCalendar\AllowedDates')->releaseYears();

        // Action lists
        $bindings['ReviewDraftUnprocessedCount'] = $this->repoReviewDraft->countUnprocessed();
        $pendingQuickReview = $this->repoQuickReview->byStatus(QuickReview::STATUS_PENDING);
        $bindings['PendingQuickReviewCount'] = count($pendingQuickReview);

        // Stats
        $bindings['ReviewLinkCount'] = $this->statsReviewLink->totalOverall();
        $bindings['RankedGameCount'] = $this->repoGameStats->totalRanked();
        $bindings['UnrankedGameCount'] = $this->repoUnranked->totalUnranked();

        // Unranked breakdown
        $bindings['UnrankedReviews2'] = $this->repoUnranked->totalByReviewCount(2);
        $bindings['UnrankedReviews1'] = $this->repoUnranked->totalByReviewCount(1);
        $bindings['UnrankedReviews0'] = $this->repoUnranked->totalByReviewCount(0);
        foreach ($allowedYears as $year) {
            $bindings['UnrankedYear'.$year] = $this->repoUnranked->totalByYear($year);
        }
        $bindings['UnrankedLowQuality'] = $this->repoUnranked->totalLowQuality();
        $bindings['AllowedYears'] = $allowedYears;

        $bindings['ProcessStatusStats'] = $this->statsReviewDraft->getProcessStatusStats();

        return view('staff.reviews.dashboard', $bindings);
    }
}
