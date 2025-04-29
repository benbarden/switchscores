<?php

namespace App\Http\Controllers\PublicSite;

use App\Domain\GameStats\DbQueries as GameStatsDbQueries;
use App\Domain\ViewBreadcrumbs\MainSite as Breadcrumbs;

use App\Models\Console;
use Illuminate\Routing\Controller as Controller;

class AboutController extends Controller
{
    public function __construct(
        private GameStatsDbQueries $dbGameStats,
        private Breadcrumbs $viewBreadcrumbs
    )
    {
    }

    public function landing()
    {
        $bindings = [];

        $bindings['crumbNav'] = $this->viewBreadcrumbs->topLevelPage('About');

        // Stats by console
        $switch1Stats = $this->dbGameStats->siteStatsByConsole(Console::ID_SWITCH_1);
        $switch2Stats = $this->dbGameStats->siteStatsByConsole(Console::ID_SWITCH_2);
        $bindings['Switch1Stats'] = $switch1Stats[0];
        $bindings['Switch2Stats'] = $switch2Stats[0];

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
