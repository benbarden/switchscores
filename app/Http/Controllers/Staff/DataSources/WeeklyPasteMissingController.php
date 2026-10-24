<?php

namespace App\Http\Controllers\Staff\DataSources;

use Illuminate\Routing\Controller as Controller;

use App\Domain\Game\Repository as GameRepository;
use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;
use App\Domain\WeeklyBatch\MissingFromPaste;
use App\Models\WeeklyPasteMissingCheck;

/**
 * Games in the DB missing from the latest weekly paste (#195). Checking a game hides it until
 * it appears in a paste again. De-listing is done in the game editor, not here.
 */
class WeeklyPasteMissingController extends Controller
{
    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private GameRepository $repoGame,
        private MissingFromPaste $missingFromPaste
    )
    {
    }

    public function show()
    {
        $pageTitle = 'Missing from the weekly paste (Upcoming)';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::dataSourcesSubpage($pageTitle))->bindings;

        $rows = $this->missingFromPaste->get();
        $bindings['NotCheckedList'] = array_values(array_filter($rows, fn ($row) => !$row->checked_on));
        $bindings['CheckedList'] = array_values(array_filter($rows, fn ($row) => $row->checked_on));
        $bindings['LatestBatches'] = $this->missingFromPaste->latestBatches();
        $bindings['HighlightGameId'] = request()->get('gameid');

        return view('staff.data-sources.differences.weekly-paste-missing', $bindings);
    }

    public function check()
    {
        $back = redirect()->route('staff.data-sources.differences.weekly-paste.missing');

        $game = $this->repoGame->find(request()->input('game_id'));
        if (!$game) {
            return $back->with('pasteMissingError', 'Game not found.');
        }

        WeeklyPasteMissingCheck::updateOrCreate(['game_id' => $game->id], ['checked_on' => now()->toDateString()]);

        return $back->with('pasteMissingSuccess', 'Checked '.$game->title.'.');
    }

    public function uncheck()
    {
        WeeklyPasteMissingCheck::where('game_id', request()->input('game_id'))->delete();

        return redirect()->route('staff.data-sources.differences.weekly-paste.missing');
    }
}
