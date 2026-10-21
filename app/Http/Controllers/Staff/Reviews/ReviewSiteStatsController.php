<?php

namespace App\Http\Controllers\Staff\Reviews;

use Illuminate\Routing\Controller as Controller;

use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;

use App\Domain\GameStats\Repository as GameStatsRepository;
use App\Domain\ReviewSite\Repository as ReviewSiteRepository;
use App\Domain\ReviewLink\Calculations as ReviewLinkCalculations;
use App\Domain\ReviewLink\Stats as ReviewLinkStats;

/**
 * Review site stats, moved from the retired owner-only Stats section (#20) so they sit
 * with the rest of Reviews and use its role.
 */
class ReviewSiteStatsController extends Controller
{
    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private GameStatsRepository $repoGameStats,
        private ReviewSiteRepository $repoReviewSite,
        private ReviewLinkCalculations $calcReviewLink,
        private ReviewLinkStats $statsReviewLink
    )
    {
    }

    public function index()
    {
        $pageTitle = 'Review site stats';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::reviewsSubpage($pageTitle))->bindings;

        // Not shown any more, but "Games reviewed" is a percentage of released games
        $releasedGameCount = $this->repoGameStats->totalReleased();
        $reviewLinkCount = $this->statsReviewLink->totalOverall();

        $reviewSitesRender = [];
        foreach ($this->repoReviewSite->getAll() as $reviewSite) {
            $reviewCount = $reviewSite->review_count;
            $reviewSitesRender[] = [
                'id' => $reviewSite->id,
                'name' => $reviewSite->name,
                'link_title' => $reviewSite->link_title,
                'review_count' => $reviewCount,
                'review_link_contrib_total' => $this->calcReviewLink->contributionPercentage($reviewCount, $reviewLinkCount),
                'review_game_completion_total' => $this->calcReviewLink->contributionPercentage($reviewCount, $releasedGameCount),
                'latest_review_date' => $reviewSite->last_review_date,
            ];
        }

        $bindings['ReviewSitesArray'] = $reviewSitesRender;

        return view('staff.reviews.review-site-stats.index', $bindings);
    }

    /**
     * Monthly review links for one site - the "View" link on the review sites list.
     */
    public function site($siteId)
    {
        $reviewSite = $this->repoReviewSite->find($siteId);
        if (!$reviewSite) abort(404);

        $pageTitle = 'Review link stats: '.$reviewSite->name;
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::reviewsReviewSitesSubpage($pageTitle))->bindings;

        $chartDataSet = $this->statsReviewLink->monthlyCountBySite($siteId);
        $bindings['ChartDataSet'] = $chartDataSet;
        $bindings['ChartColourGroups'] = ceil(count($chartDataSet) / 6);

        return view('staff.reviews.review-site-stats.site', $bindings);
    }
}
