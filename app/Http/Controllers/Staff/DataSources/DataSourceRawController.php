<?php

namespace App\Http\Controllers\Staff\DataSources;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;

class DataSourceRawController extends Controller
{
    use SwitchServices;

    public function show($sourceId)
    {
        $dataSource = $this->getServiceDataSource()->find($sourceId);

        if (!$dataSource) abort(404);

        $pageTitle = $dataSource->name.' - Raw items';

        $bindings = [];

        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        $bindings['ItemList'] = $this->getServiceDataSourceRaw()->getBySourceId($sourceId);
        $bindings['jsInitialSort'] = "[ 0, 'asc' ]";

        return view('staff.data-sources.raw.list', $bindings);
    }

    public function view($sourceId, $itemId)
    {
        $dataSource = $this->getServiceDataSource()->find($sourceId);
        $dsRawItem = $this->getServiceDataSourceRaw()->find($itemId);

        if (!$dataSource) abort(404);
        if (!$dsRawItem) abort(404);

        $pageTitle = $dsRawItem->title;

        $bindings = [];

        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        $bindings['DSRawItem'] = $dsRawItem;

        // For breadcrumbs, mainly
        $bindings['SourceId'] = $sourceId;
        $bindings['DataSource'] = $dataSource;

        return view('staff.data-sources.raw.view', $bindings);

    }
}
