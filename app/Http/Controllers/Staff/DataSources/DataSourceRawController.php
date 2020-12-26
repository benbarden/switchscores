<?php

namespace App\Http\Controllers\Staff\DataSources;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;
use App\Traits\StaffView;

class DataSourceRawController extends Controller
{
    use SwitchServices;
    use StaffView;

    public function show($sourceId)
    {
        $dataSource = $this->getServiceDataSource()->find($sourceId);
        if (!$dataSource) abort(404);

        $bindings = $this->getBindingsDataSourcesSubpage($dataSource->name.' - Raw items');

        $bindings['ItemList'] = $this->getServiceDataSourceRaw()->getBySourceId($sourceId);

        return view('staff.data-sources.raw.list', $bindings);
    }

    public function view($sourceId, $itemId)
    {
        $dataSource = $this->getServiceDataSource()->find($sourceId);
        $dsRawItem = $this->getServiceDataSourceRaw()->find($itemId);

        if (!$dataSource) abort(404);
        if (!$dsRawItem) abort(404);

        $bindings = $this->getBindingsDataSourcesListRawSubpage($dsRawItem->title, $dataSource);

        $bindings['DSRawItem'] = $dsRawItem;

        return view('staff.data-sources.raw.view', $bindings);

    }
}
