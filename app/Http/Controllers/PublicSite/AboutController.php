<?php

namespace App\Http\Controllers\PublicSite;

use Illuminate\Routing\Controller as Controller;

use App\Domain\View\Breadcrumbs\PublicBreadcrumbs;
use App\Domain\View\PageBuilders\PublicPageBuilder;

use App\Domain\GameStats\DbQueries as GameStatsDbQueries;

use App\Models\Console;

class AboutController extends Controller
{
    public function __construct(
        private PublicPageBuilder $pageBuilder,
        private GameStatsDbQueries $dbGameStats,
    )
    {
    }

    public function landing()
    {
        $pageTitle = 'About Switch Scores';
        $topTitleOverride = 'About';
        $bindings = $this->pageBuilder->build($pageTitle, PublicBreadcrumbs::topLevel($pageTitle), topTitleOverride: $topTitleOverride)->bindings;

        // Stats by console
        $switch1Stats = $this->dbGameStats->siteStatsByConsole(Console::ID_SWITCH_1);
        $switch2Stats = $this->dbGameStats->siteStatsByConsole(Console::ID_SWITCH_2);
        $bindings['Switch1Stats'] = $switch1Stats[0];
        $bindings['Switch2Stats'] = $switch2Stats[0];

        return view('public.about.landing', $bindings);
    }

    public function changelog()
    {
        $pageTitle = 'Changelog';
        $bindings = $this->pageBuilder->build($pageTitle, PublicBreadcrumbs::topLevel($pageTitle))->bindings;

        return view('public.about.changelog', $bindings);
    }

    public function inviteRequestSuccess()
    {
        $pageTitle = 'Invite request successful';
        $bindings = $this->pageBuilder->build($pageTitle, PublicBreadcrumbs::topLevel($pageTitle))->bindings;

        return view('auth.invite-code-requested', $bindings);
    }

    public function inviteRequestFailure()
    {
        $pageTitle = 'Invite request failed';
        $bindings = $this->pageBuilder->build($pageTitle, PublicBreadcrumbs::topLevel($pageTitle))->bindings;

        return view('auth.invite-code-duplicate', $bindings);
    }
}
