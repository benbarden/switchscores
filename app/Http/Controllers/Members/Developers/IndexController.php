<?php

namespace App\Http\Controllers\Members\Developers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as Controller;

use App\Domain\View\Breadcrumbs\MembersBreadcrumbs;
use App\Domain\View\PageBuilders\MembersPageBuilder;

use App\Domain\Game\Repository as GameRepository;
use App\Domain\GameLists\Repository as GameListsRepository;
use App\Models\Console;

class IndexController extends Controller
{
    public function __construct(
        private MembersPageBuilder $pageBuilder,
        private GameListsRepository $repoGameLists,
        private GameRepository $repoGame,
    )
    {
    }

    public function index()
    {
        $pageTitle = 'Developers';
        $bindings = $this->pageBuilder->build($pageTitle, MembersBreadcrumbs::developersDashboard())->bindings;

        return view('members.developers.index', $bindings);
    }

    public function switchWeekly()
    {
        $pageTitle = 'Upcoming games (Switch Weekly)';
        $bindings = $this->pageBuilder->build($pageTitle, MembersBreadcrumbs::developersSubpage($pageTitle))->bindings;

        $daysS1 = 7;
        $daysS2 = 30;

        $upcomingGamesS1 = $this->repoGameLists->upcomingSwitchWeekly(Console::ID_SWITCH_1, $daysS1);
        $upcomingGamesS2 = $this->repoGameLists->upcomingSwitchWeekly(Console::ID_SWITCH_2, $daysS2);

        $bindings['UpcomingGamesS1'] = $upcomingGamesS1;
        $bindings['UpcomingGamesS2'] = $upcomingGamesS2;
        $bindings['DaysLimitS1'] = $daysS1;
        $bindings['DaysLimitS2'] = $daysS2;

        return view('members.developers.switch-weekly', $bindings);
    }

    public function hanafudaReport(Request $request)
    {
        $pageTitle = 'Hanufuda Report: Database updates';
        $bindings = $this->pageBuilder->build($pageTitle, MembersBreadcrumbs::developersSubpage($pageTitle))->bindings;

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
        $bindings['UpcomingAll'] = $bindings['UpcomingS1']->concat($bindings['UpcomingS2'])->sortBy('eu_release_date')->values();
        $bindings['ReleasedS1'] = $this->repoGameLists->releasedByBatch(Console::ID_SWITCH_1, $selectedDate);
        $bindings['ReleasedS2'] = $this->repoGameLists->releasedByBatch(Console::ID_SWITCH_2, $selectedDate);

        return view('members.developers.hanafuda-report', $bindings);
    }
}
