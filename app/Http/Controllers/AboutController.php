<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as Controller;

use App\Domain\GameStats\Repository as GameStatsRepository;
use App\Domain\ViewBreadcrumbs\MainSite as Breadcrumbs;

use App\Traits\SwitchServices;

class AboutController extends Controller
{
    use SwitchServices;

    protected $repoGameStats;
    protected $viewBreadcrumbs;

    public function __construct(
        GameStatsRepository $repoGameStats,
        Breadcrumbs $viewBreadcrumbs
    )
    {
        $this->repoGameStats = $repoGameStats;
        $this->viewBreadcrumbs = $viewBreadcrumbs;
    }

    public function landing()
    {
        $bindings = [];

        $bindings['crumbNav'] = $this->viewBreadcrumbs->topLevelPage('About');

        // Quick stats
        $totalReleased = $this->repoGameStats->totalReleased();
        $totalRanked = $this->repoGameStats->totalRanked();
        $totalReviews = $this->getServiceReviewLink()->countActive();
        $totalLowQuality = $this->repoGameStats->totalLowQuality();

        $bindings['TotalReleasedGames'] = $totalReleased;
        $bindings['TotalRanked'] = $totalRanked;
        $bindings['TotalReviews'] = $totalReviews;
        $bindings['TotalLowQualityGames'] = $totalLowQuality;

        if ($totalReleased > 0) {
            $lowQualityPercent = round(($totalLowQuality / $totalReleased) * 100, 2);
            $bindings['LowQualityPercent'] = $lowQualityPercent.'%';
        }

        $bindings['TopTitle'] = 'About';
        $bindings['PageTitle'] = 'About Switch Scores';

        return view('about.landing', $bindings);
    }

    public function changelog()
    {
        $bindings = [];

        $bindings['crumbNav'] = $this->viewBreadcrumbs->aboutSubpage('Changelog');

        $bindings['TopTitle'] = 'Changelog';
        $bindings['PageTitle'] = 'Changelog';

        return view('about.changelog', $bindings);
    }
}
