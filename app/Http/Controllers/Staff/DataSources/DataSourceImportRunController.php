<?php

namespace App\Http\Controllers\Staff\DataSources;

use Illuminate\Routing\Controller as Controller;

use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;
use App\Models\DataSource;
use App\Models\DataSourceImportLog;

use App\Domain\DataSourceImportRun\Repository as DataSourceImportRunRepository;
use App\Domain\DataSourceImportLog\Repository as DataSourceImportLogRepository;

class DataSourceImportRunController extends Controller
{
    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private DataSourceImportRunRepository $repoImportRun,
        private DataSourceImportLogRepository $repoImportLog
    ) {
    }

    public function index()
    {
        $pageTitle = 'Import runs';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::dataSourcesImportRunsSubpage($pageTitle))->bindings;

        $sourceId = DataSource::DSID_NINTENDO_CO_UK;
        $runs = $this->repoImportRun->getAllBySourcePaginated($sourceId);
        $runIds = $runs->pluck('id')->toArray();
        $runCounts = $runIds ? $this->repoImportLog->getCountsByRunIds($runIds) : [];

        $bindings['Runs'] = $runs;
        $bindings['RunCounts'] = $runCounts;

        return view('staff.data-sources.import-runs.list', $bindings);
    }

    public function view($runId)
    {
        $run = $this->repoImportRun->find($runId);
        if (!$run) abort(404);

        $pageTitle = 'Import run #'.$runId;
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::dataSourcesImportRunDetailSubpage($pageTitle))->bindings;

        $logEntries = $this->repoImportLog->getByRunId($runId);
        $counts = $this->repoImportLog->getCountsByRunIds([$runId])[$runId] ?? [];

        $bindings['Run'] = $run;
        $bindings['LogEntries'] = $logEntries;
        $bindings['Counts'] = $counts;

        return view('staff.data-sources.import-runs.view', $bindings);
    }
}
