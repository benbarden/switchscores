<?php

namespace App\Domain\DataSourceImportRun;

use App\Models\DataSourceImportRun;

class Repository
{
    public function startRun($sourceId): DataSourceImportRun
    {
        $run = new DataSourceImportRun();
        $run->source_id  = $sourceId;
        $run->status     = DataSourceImportRun::STATUS_RUNNING;
        $run->started_at = now();
        $run->save();
        return $run;
    }

    public function completeRun(DataSourceImportRun $run): void
    {
        $run->status       = DataSourceImportRun::STATUS_COMPLETED;
        $run->completed_at = now();
        $run->save();
    }

    public function failRun(DataSourceImportRun $run): void
    {
        $run->status       = DataSourceImportRun::STATUS_FAILED;
        $run->completed_at = now();
        $run->save();
    }

    public function getRecentDaysBySource($sourceId, $days = 21)
    {
        return DataSourceImportRun::where('source_id', $sourceId)
            ->where('started_at', '>=', now()->subDays($days))
            ->orderBy('started_at', 'desc')
            ->get();
    }

    public function getAllBySourcePaginated($sourceId, $perPage = 30)
    {
        return DataSourceImportRun::where('source_id', $sourceId)
            ->orderBy('started_at', 'desc')
            ->paginate($perPage);
    }

    public function find($runId)
    {
        return DataSourceImportRun::find($runId);
    }

    public function getRecentBySource($sourceId, $limit = 20)
    {
        return DataSourceImportRun::where('source_id', $sourceId)
            ->orderBy('started_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
