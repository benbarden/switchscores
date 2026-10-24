<?php

namespace App\Http\Controllers\Staff\DataSources;

use Illuminate\Routing\Controller as Controller;

use App\Domain\DataSource\NintendoCoUk\StoreEditions;
use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;
use App\Models\Console;

/**
 * Store editions (#161): linked games with a deluxe edition, bundle or upgrade pack on the
 * Nintendo store, from the price API data. Read only.
 */
class NintendoCoUkStoreEditionsController extends Controller
{
    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private StoreEditions $storeEditions
    )
    {
    }

    public function show()
    {
        $consoleId = (int) request()->get('console', Console::ID_SWITCH_2);
        if (!in_array($consoleId, [Console::ID_SWITCH_1, Console::ID_SWITCH_2], true)) abort(404);

        $pageTitle = 'Store editions';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::dataSourcesSubpage($pageTitle))->bindings;

        $bindings['ConsoleId'] = $consoleId;
        $bindings['GameList'] = $this->storeEditions->gamesWithEditions($consoleId);

        return view('staff.data-sources.nintendo-co-uk.store-editions', $bindings);
    }
}
