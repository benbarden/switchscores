<?php

namespace App\Http\Controllers\Staff\DataSources;

use Illuminate\Routing\Controller as Controller;

use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;

use App\Domain\DataSource\Repository as DataSourceRepository;
use App\Domain\DataSourceRaw\Repository as DataSourceRawRepository;

class DataSourceRawController extends Controller
{
    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private DataSourceRepository $repoDataSource,
        private DataSourceRawRepository $repoDataSourceRaw
    ){
    }

    public function show($sourceId)
    {
        $dataSource = $this->repoDataSource->find($sourceId);
        if (!$dataSource) abort(404);

        $pageTitle = $dataSource->name.' - Raw items';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::dataSourcesSubpage($pageTitle))->bindings;

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
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::dataSourcesListRawSubpage($pageTitle, $dataSource))->bindings;

        if ($dsRawItem) {
            $sourceDataRaw = json_decode($dsRawItem->source_data_json, true);
            $bindings['SourceDataRaw'] = $sourceDataRaw;
        }

        return view('staff.data-sources.raw.view', $bindings);

    }
}
