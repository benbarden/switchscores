<?php

namespace App\Http\Controllers\Staff\Games;

use Illuminate\Routing\Controller as Controller;

use App\Domain\GameLists\Repository as GameListsRepository;
use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;

/**
 * Games to release (#197): games past their release date and not marked released, linked from
 * the staff home tile. Releasing uses the Release hub's toggle, so the same future-date guard
 * and cache clear apply.
 */
class GamesToReleaseController extends Controller
{
    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private GameListsRepository $repoGameLists
    )
    {
    }

    public function show()
    {
        $pageTitle = 'Games to release';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::gamesSubpage($pageTitle))->bindings;

        $bindings['GameList'] = $this->repoGameLists->gamesForRelease();

        return view('staff.games.games-to-release.show', $bindings);
    }
}
