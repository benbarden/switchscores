<?php

namespace App\Domain\WeeklyBatch;

use App\Models\WeeklyBatch;

class Repository
{
    public function create(string $batchDate): WeeklyBatch
    {
        $batch = new WeeklyBatch();
        $batch->batch_date = $batchDate;
        $batch->status = WeeklyBatch::STATUS_SETUP;
        $batch->save();
        return $batch;
    }

    public function find(int $id): ?WeeklyBatch
    {
        return WeeklyBatch::find($id);
    }

    public function getAll()
    {
        return WeeklyBatch::orderBy('batch_date', 'desc')->get();
    }

    public function getAllPaginated(int $perPage = 20)
    {
        return WeeklyBatch::orderBy('batch_date', 'desc')->paginate($perPage);
    }

    public function markInProgress(WeeklyBatch $batch): void
    {
        $batch->status = WeeklyBatch::STATUS_IN_PROGRESS;
        $batch->save();
    }

    public function markComplete(WeeklyBatch $batch): void
    {
        $batch->status = WeeklyBatch::STATUS_COMPLETE;
        $batch->save();
    }
}
