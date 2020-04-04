<?php

namespace App\Http\Controllers\Staff\DataSources;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;

class DashboardController extends Controller
{
    use SwitchServices;

    public function show()
    {
        $pageTitle = 'Data sources dashboard';

        $bindings = [];

        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        $bindings['DataSources'] = $this->getServiceDataSource()->getAll();

        // Stats: Nintendo.co.uk

        // Unlinked items
        $unlinkedItemList = $this->getServiceDataSourceParsed()->getAllNintendoCoUkWithNoGameId();
        $bindings['NintendoCoUkUnlinkedCount'] = $unlinkedItemList->count();

        // Ignored items
        $ignoreIdList = $this->getServiceDataSourceIgnore()->getNintendoCoUkLinkIdList();
        $ignoredItemList = $this->getServiceDataSourceParsed()->getAllNintendoCoUkInLinkIdList($ignoreIdList);
        $bindings['NintendoCoUkIgnoredCount'] = $ignoredItemList->count();

        return view('staff.data-sources.dashboard', $bindings);
    }
}
