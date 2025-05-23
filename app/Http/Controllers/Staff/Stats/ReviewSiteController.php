<?php

namespace App\Http\Controllers\Staff\Stats;

use Illuminate\Routing\Controller as Controller;

use App\Domain\GameStats\Repository as GameStatsRepository;
use App\Domain\Unranked\Repository as UnrankedRepository;
use App\Domain\ReviewSite\Repository as ReviewSiteRepository;
use App\Domain\ReviewLink\Calculations as ReviewLinkCalculations;
use App\Domain\ReviewLink\Stats as ReviewLinkStats;

class ReviewSiteController extends Controller
{
    public function __construct(
        private GameStatsRepository $repoGameStats,
        private UnrankedRepository $repoUnranked,
        private ReviewSiteRepository $repoReviewSite,
        private ReviewLinkCalculations $calcReviewLink,
        private ReviewLinkStats $statsReviewLink
    )
    {
    }

    public function show()
    {
        $pageTitle = 'Review site stats';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->statsSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['RankedGameCount'] = $this->repoGameStats->totalRanked();
        $bindings['UnrankedGameCount'] = $this->repoUnranked->totalUnranked();

        $releasedGameCount = $this->repoGameStats->totalReleased();
        $reviewLinkCount = $this->statsReviewLink->totalOverall();

        $bindings['ReleasedGameCount'] = $releasedGameCount;
        $bindings['ReviewLinkCount'] = $reviewLinkCount;

        $reviewSitesActive = $this->repoReviewSite->getAll();
        $reviewSitesRender = [];

        foreach ($reviewSitesActive as $reviewSite) {

            $id = $reviewSite->id;
            $name = $reviewSite->name;
            $linkTitle = $reviewSite->link_title;
            $reviewCount = $reviewSite->review_count;
            $latestReviewDate = $reviewSite->last_review_date;

            $reviewLinkContribTotal = $this->calcReviewLink->contributionPercentage($reviewCount, $reviewLinkCount);
            $reviewGameCompletionTotal = $this->calcReviewLink->contributionPercentage($reviewCount, $releasedGameCount);

            $reviewSitesRender[] = [
                'id' => $id,
                'name' => $name,
                'link_title' => $linkTitle,
                'review_count' => $reviewCount,
                'review_link_contrib_total' => $reviewLinkContribTotal,
                'review_game_completion_total' => $reviewGameCompletionTotal,
                'latest_review_date' => $latestReviewDate,
            ];

        }

        $bindings['ReviewSitesArray'] = $reviewSitesRender;

        return view('staff.stats.reviewSites', $bindings);
    }
}
