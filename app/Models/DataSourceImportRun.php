<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataSourceImportRun extends Model
{
    const STATUS_RUNNING   = 'running';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED    = 'failed';

    protected $table = 'data_source_import_runs';

    public $timestamps = false;

    protected $fillable = [
        'source_id', 'status', 'started_at', 'completed_at'
    ];

    protected $casts = [
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function logs()
    {
        return $this->hasMany(DataSourceImportLog::class, 'run_id');
    }

    public function durationSeconds()
    {
        if (!$this->completed_at) return null;
        return $this->completed_at->diffInSeconds($this->started_at);
    }
}
