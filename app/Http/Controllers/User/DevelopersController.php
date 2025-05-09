<?php

namespace App\Http\Controllers\User;

use App\Models\Console;
use Illuminate\Routing\Controller as Controller;

use App\Domain\GameLists\Repository as GameListsRepository;

class DevelopersController extends Controller
{
    public function __construct(
        private GameListsRepository $repoGameLists,
    )
    {
    }

    public function index()
    {
        $pageTitle = 'Developers';
        $breadcrumbs = resolve('View/Breadcrumbs/Member')->topLevelPage($pageTitle);
        $bindings = resolve('View/Bindings/Member')->setBreadcrumbs($breadcrumbs)->generateMember($pageTitle);

        return view('user.developers.index', $bindings);
    }

    public function switchWeekly()
    {
        $pageTitle = 'Upcoming games (Switch Weekly)';
        $breadcrumbs = resolve('View/Breadcrumbs/Member')->developersSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Member')->setBreadcrumbs($breadcrumbs)->generateMember($pageTitle);

        $daysS1 = 7;
        $daysS2 = 30;

        $upcomingGamesS1 = $this->repoGameLists->upcomingSwitchWeekly(Console::ID_SWITCH_1, $daysS1);
        $upcomingGamesS2 = $this->repoGameLists->upcomingSwitchWeekly(Console::ID_SWITCH_2, $daysS2);

        $bindings['UpcomingGamesS1'] = $upcomingGamesS1;
        $bindings['UpcomingGamesS2'] = $upcomingGamesS2;
        $bindings['DaysLimitS1'] = $daysS1;
        $bindings['DaysLimitS2'] = $daysS2;

        return view('user.developers.switch-weekly', $bindings);
    }
}
