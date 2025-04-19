<?php

namespace App\Http\Controllers\Staff\DataSources;

use Illuminate\Routing\Controller as Controller;

use App\Domain\DataSource\Repository as DataSourceRepository;
use App\Domain\DataSourceRaw\Repository as DataSourceRawRepository;

class DataSourceRawController extends Controller
{
    public function __construct(
        private DataSourceRepository $repoDataSource,
        private DataSourceRawRepository $repoDataSourceRaw
    ){
    }

    public function show($sourceId)
    {
        $dataSource = $this->repoDataSource->find($sourceId);
        if (!$dataSource) abort(404);

        $pageTitle = $dataSource->name.' - Raw items';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->dataSourcesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['ItemList'] = $this->repoDataSourceRaw->getBySourceId($sourceId);

        return view('staff.data-sources.raw.list', $bindings);
    }

    public function view($sourceId, $itemId)
    {
        $dataSource = $this->repoDataSource->find($sourceId);
        $dsRawItem = $this->repoDataSourceRaw->find($itemId);

        if (!$dataSource) abort(404);
        if (!$dsRawItem) abort(404);

        $pageTitle = $dsRawItem->title;
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->dataSourcesListRawSubpage($pageTitle, $dataSource);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['DSRawItem'] = $dsRawItem;

        return view('staff.data-sources.raw.view', $bindings);

    }
}
