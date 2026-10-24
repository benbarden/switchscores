<?php

namespace App\Http\Controllers\Staff\DataSources;

use Illuminate\Routing\Controller as Controller;

use App\Domain\Game\Repository as GameRepository;
use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;
use App\Domain\WeeklyBatch\DateDifferences;
use App\Models\WeeklyPasteDateIgnore;

/**
 * Release date differences from the weekly paste (#194). Nothing changes without a click:
 * Apply copies the pasted date onto the game, Ignore hides that date for that game.
 */
class WeeklyPasteDateDifferencesController extends Controller
{
    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private GameRepository $repoGame,
        private DateDifferences $dateDifferences
    )
    {
    }

    public function show()
    {
        $pageTitle = 'Differences: EU release date - Weekly paste';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::dataSourcesSubpage($pageTitle))->bindings;

        $bindings['DifferenceList'] = $this->dateDifferences->get();
        $bindings['IgnoredCount'] = WeeklyPasteDateIgnore::count();
        $bindings['HighlightGameId'] = request()->get('gameid');

        return view('staff.data-sources.differences.weekly-paste-release-date', $bindings);
    }

    public function apply()
    {
        $request = request();
        $back = redirect()->route('staff.data-sources.differences.weekly-paste.release-date');

        // The page may be out of date: check the paste still shows this date for this game
        $row = $this->currentDifference($request->input('game_id'), $request->input('release_date'));
        if (!$row) {
            return $back->with('pasteDatesError', 'The latest paste no longer shows that date for this game.');
        }

        $game = $this->repoGame->find($row->id);
        $game->eu_release_date = $row->paste_release_date;
        $game->release_year = substr($row->paste_release_date, 0, 4);
        $game->save();

        $this->repoGame->clearCacheCoreData($game->id);

        return $back->with('pasteDatesSuccess', 'Applied '.$row->paste_release_date.' to '.$game->title.'.');
    }

    public function ignore()
    {
        $request = request();
        $back = redirect()->route('staff.data-sources.differences.weekly-paste.release-date');

        $row = $this->currentDifference($request->input('game_id'), $request->input('release_date'));
        if (!$row) {
            return $back->with('pasteDatesError', 'The latest paste no longer shows that date for this game.');
        }

        WeeklyPasteDateIgnore::firstOrCreate(['game_id' => $row->id, 'release_date' => $row->paste_release_date]);

        return $back->with('pasteDatesSuccess', 'Ignored '.$row->paste_release_date.' for '.$row->title.'.');
    }

    public function ignored()
    {
        $pageTitle = 'Ignored dates - Weekly paste';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::dataSourcesSubpage($pageTitle))->bindings;

        $bindings['IgnoredList'] = WeeklyPasteDateIgnore::with('game')->orderBy('id', 'desc')->get();

        return view('staff.data-sources.differences.weekly-paste-release-date-ignored', $bindings);
    }

    public function removeIgnored()
    {
        WeeklyPasteDateIgnore::where('id', request()->input('id'))->delete();

        return redirect()->route('staff.data-sources.differences.weekly-paste.release-date.ignored')
            ->with('pasteDatesSuccess', 'Removed. The date will show again if the latest paste still has it.');
    }

    private function currentDifference($gameId, $releaseDate): ?object
    {
        if (!$gameId || !$releaseDate) return null;

        foreach ($this->dateDifferences->get($gameId) as $row) {
            if ($row->paste_release_date == $releaseDate) return $row;
        }
        return null;
    }
}
