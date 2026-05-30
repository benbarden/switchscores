<?php

namespace App\Http\Controllers\Staff\Games\WeeklyUpdates;

use Illuminate\Routing\Controller as Controller;

use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;
use App\Domain\WeeklyBatch\Repository as WeeklyBatchRepository;
use App\Domain\WeeklyBatchItem\Repository as WeeklyBatchItemRepository;
use App\Domain\WeeklyBatchRawPage\Repository as WeeklyBatchRawPageRepository;

class WeeklyBatchController extends Controller
{
    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private WeeklyBatchRepository $repoBatch,
        private WeeklyBatchItemRepository $repoItem,
        private WeeklyBatchRawPageRepository $repoRawPage
    ) {
    }

    public function index()
    {
        $pageTitle = 'Weekly updates';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::gamesSubpage($pageTitle))->bindings;

        $batches = $this->repoBatch->getAllPaginated();

        $batchIds = $batches->pluck('id')->toArray();
        $allCounts = $batchIds ? $this->repoItem->getAllStatusCountsForBatches($batchIds) : [];

        $bindings['Batches'] = $batches;
        $bindings['AllCounts'] = $allCounts;

        return view('staff.games.weekly-updates.index', $bindings);
    }

    public function create()
    {
        $pageTitle = 'New weekly batch';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::weeklyUpdatesSubpage($pageTitle))->bindings;

        return view('staff.games.weekly-updates.create', $bindings);
    }

    public function store()
    {
        $batchDate = request('batch_date');

        // TODO: validate date format and uniqueness

        $batch = $this->repoBatch->create($batchDate);

        return redirect()->route('staff.games.weekly-updates.show', ['batchId' => $batch->id]);
    }

    public function show($batchId)
    {
        $batch = $this->repoBatch->find($batchId);
        if (!$batch) abort(404);

        $pageTitle = 'Weekly update: '.$batch->batch_date->format('d M Y');
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::weeklyUpdatesSubpage($pageTitle))->bindings;

        $allCounts    = $this->repoItem->getAllStatusCounts($batchId);
        $rawPageCounts = $this->repoRawPage->getCountsForBatch($batchId);

        $bindings['Batch']          = $batch;
        $bindings['AllCounts']      = $allCounts;
        $bindings['RawPageCounts']  = $rawPageCounts;

        $lists = [
            ['console' => 'switch-2', 'list_type' => 'new',      'label' => 'Switch 2 New'],
            ['console' => 'switch-2', 'list_type' => 'upcoming',  'label' => 'Switch 2 Upcoming'],
            ['console' => 'switch-1', 'list_type' => 'new',      'label' => 'Switch 1 New'],
            ['console' => 'switch-1', 'list_type' => 'upcoming',  'label' => 'Switch 1 Upcoming'],
        ];
        $bindings['Lists'] = $lists;

        return view('staff.games.weekly-updates.show', $bindings);
    }
}
