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
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->dataSourcesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['ItemList'] = $this->getServiceDataSourceRaw()->getBySourceId($sourceId);

        return view('staff.data-sources.raw.list', $bindings);
    }

    public function view($sourceId, $itemId)
    {
        $dataSource = $this->getServiceDataSource()->find($sourceId);
        $dsRawItem = $this->getServiceDataSourceRaw()->find($itemId);

        if (!$dataSource) abort(404);
        if (!$dsRawItem) abort(404);

        $pageTitle = $dsRawItem->title;
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->dataSourcesListRawSubpage($pageTitle, $dataSource);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['DSRawItem'] = $dsRawItem;

        return view('staff.data-sources.raw.view', $bindings);

    }
}
