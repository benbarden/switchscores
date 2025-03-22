<?php

namespace App\Http\Controllers\PublicSite;

use App\Domain\GameStats\Repository as GameStatsRepository;
use App\Domain\ReviewLink\Stats as ReviewLinkStats;
use App\Domain\ViewBreadcrumbs\MainSite as Breadcrumbs;

use App\Traits\SwitchServices;

use Illuminate\Routing\Controller as Controller;

class AboutController extends Controller
{
    use SwitchServices;

    public function __construct(
        private GameStatsRepository $repoGameStats,
        private ReviewLinkStats $statsReviewLink,
        private Breadcrumbs $viewBreadcrumbs
    )
    {
    }

    public function landing()
    {
        $bindings = [];

        $bindings['crumbNav'] = $this->viewBreadcrumbs->topLevelPage('About');

        // Quick stats
        $totalReleased = $this->repoGameStats->totalReleased();
        $totalRanked = $this->repoGameStats->totalRanked();
        $totalReviews = $this->statsReviewLink->totalOverall();
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

        return view('public.about.landing', $bindings);
    }

    public function changelog()
    {
        $bindings = [];

        $bindings['crumbNav'] = $this->viewBreadcrumbs->aboutSubpage('Changelog');

        $bindings['TopTitle'] = 'Changelog';
        $bindings['PageTitle'] = 'Changelog';

        return view('public.about.changelog', $bindings);
    }

    public function inviteRequestSuccess()
    {
        $bindings = [];

        $bindings['crumbNav'] = $this->viewBreadcrumbs->topLevelPage('Invite request');

        $bindings['TopTitle'] = 'Invite request successful';
        $bindings['PageTitle'] = 'Invite request successful';

        return view('auth.invite-code-requested', $bindings);
    }

    public function inviteRequestFailure()
    {
        $bindings = [];

        $bindings['crumbNav'] = $this->viewBreadcrumbs->topLevelPage('Invite request');

        $bindings['TopTitle'] = 'Invite request failure';
        $bindings['PageTitle'] = 'Invite request failure';

        return view('auth.invite-code-duplicate', $bindings);
    }
}
