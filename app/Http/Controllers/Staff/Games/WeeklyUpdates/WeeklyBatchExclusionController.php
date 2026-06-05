<?php

namespace App\Http\Controllers\Staff\Games\WeeklyUpdates;

use Illuminate\Routing\Controller as Controller;

use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;
use App\Domain\WeeklyBatchExclusion\Repository as WeeklyBatchExclusionRepository;

class WeeklyBatchExclusionController extends Controller
{
    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private WeeklyBatchExclusionRepository $repoExclusion
    ) {
    }

    public function index()
    {
        $pageTitle = 'Weekly update exclusions';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::gamesSubpage($pageTitle))->bindings;

        $bindings['Exclusions'] = $this->repoExclusion->getPaginated(25);

        return view('staff.games.weekly-updates.exclusions.index', $bindings);
    }

    public function remove($exclusionId)
    {
        $exclusion = $this->repoExclusion->find($exclusionId);
        if (!$exclusion) abort(404);

        $this->repoExclusion->remove($exclusionId);

        return redirect()->route('staff.games.weekly-updates.exclusions.index')
            ->with('success', "Exclusion for \"{$exclusion->title}\" removed.");
    }
}
