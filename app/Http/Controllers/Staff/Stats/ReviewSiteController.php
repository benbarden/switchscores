<?php

namespace App\Http\Controllers\Staff\Stats;

use Illuminate\Routing\Controller as Controller;

use App\Domain\ViewBreadcrumbs\Staff as Breadcrumbs;

use App\Domain\FeaturedGame\Repository as FeaturedGameRepository;
use App\Domain\GameStats\Repository as GameStatsRepository;
use App\Domain\ReviewSite\Repository as ReviewSiteRepository;

use App\Traits\SwitchServices;
use App\Traits\StaffView;

class ReviewSiteController extends Controller
{
    use SwitchServices;
    use StaffView;

    protected $viewBreadcrumbs;
    protected $repoFeaturedGames;
    protected $repoGameStats;
    protected $repoReviewSite;

    public function __construct(
        Breadcrumbs $viewBreadcrumbs,
        FeaturedGameRepository $featuredGames,
        GameStatsRepository $repoGameStats,
        ReviewSiteRepository $repoReviewSite
    )
    {
        $this->viewBreadcrumbs = $viewBreadcrumbs;
        $this->repoFeaturedGames = $featuredGames;
        $this->repoGameStats = $repoGameStats;
        $this->repoReviewSite = $repoReviewSite;
    }

    public function show()
    {
        $bindings = $this->getBindings('Review site stats');
        $bindings['crumbNav'] = $this->viewBreadcrumbs->statsSubpage('Review site stats');

        $serviceReviewLinks = $this->getServiceReviewLink();
        $serviceTopRated = $this->getServiceTopRated();
        $serviceReviewStats = $this->getServiceReviewStats();

        $bindings['RankedGameCount'] = $this->repoGameStats->totalRanked();
        $bindings['UnrankedGameCount'] = $serviceTopRated->getUnrankedCount();

        $releasedGameCount = $this->repoGameStats->totalReleased();
        $reviewLinkCount = $serviceReviewLinks->countActive();

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

            $reviewLinkContribTotal = $serviceReviewStats->calculateContributionPercentage($reviewCount, $reviewLinkCount);
            $reviewGameCompletionTotal = $serviceReviewStats->calculateContributionPercentage($reviewCount, $releasedGameCount);

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
