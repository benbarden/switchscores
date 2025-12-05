<?php

namespace App\Http\Controllers\User;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Http\Request;

use App\Models\Console;

use App\Domain\GameLists\Repository as GameListsRepository;
use App\Domain\Game\Repository as GameRepository;

class DevelopersController extends Controller
{
    public function __construct(
        private GameListsRepository $repoGameLists,
        private GameRepository $repoGame,
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

    public function hanafudaReport(Request $request)
    {
        $pageTitle = 'Hanufuda Report: Database updates';
        $breadcrumbs = resolve('View/Breadcrumbs/Member')->developersSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Member')->setBreadcrumbs($breadcrumbs)->generateMember($pageTitle);

        $batchDates = $this->repoGame->getBatchDates();
        // Determine the selected batch date
        $selectedDate = $request->query('batch_date');

        if (!$selectedDate) {
            // Default: latest batch
            $selectedDate = $batchDates->first();
        }

        $bindings['SelectedDate'] = $selectedDate;
        $bindings['BatchDateList'] = $batchDates;

        $bindings['UpcomingS1'] = $this->repoGameLists->upcomingByBatch(Console::ID_SWITCH_1, $selectedDate);
        $bindings['UpcomingS2'] = $this->repoGameLists->upcomingByBatch(Console::ID_SWITCH_2, $selectedDate);
        $bindings['ReleasedS1'] = $this->repoGameLists->releasedByBatch(Console::ID_SWITCH_1, $selectedDate);
        $bindings['ReleasedS2'] = $this->repoGameLists->releasedByBatch(Console::ID_SWITCH_2, $selectedDate);

        return view('user.developers.hanafuda-report', $bindings);
    }
}
