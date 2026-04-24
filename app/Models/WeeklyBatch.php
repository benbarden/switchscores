<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeeklyBatch extends Model
{
    const STATUS_SETUP       = 'setup';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETE    = 'complete';

    protected $table = 'weekly_batches';

    protected $fillable = ['batch_date', 'status'];

    protected $casts = [
        'batch_date' => 'date',
    ];

    public function items()
    {
        return $this->hasMany(WeeklyBatchItem::class, 'batch_id');
    }

    public function rawPages()
    {
        return $this->hasMany(WeeklyBatchRawPage::class, 'batch_id');
    }
}
