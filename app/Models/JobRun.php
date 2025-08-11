<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobRun extends Model {
    protected $fillable = [
        'group_key', 'command', 'status', 'exit_code', 'queued_at', 'started_at', 'finished_at', 'duration_ms', 'output'];
    protected $casts = [
        'queued_at' => 'datetime', 'started_at' => 'datetime', 'finished_at' => 'datetime'];
}
