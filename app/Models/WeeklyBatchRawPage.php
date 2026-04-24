<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeeklyBatchRawPage extends Model
{
    protected $table = 'weekly_batch_raw_pages';

    protected $fillable = [
        'batch_id', 'console', 'list_type', 'page_number', 'raw_content', 'parsed_at',
    ];

    protected $casts = [
        'parsed_at' => 'datetime',
    ];

    public function batch()
    {
        return $this->belongsTo(WeeklyBatch::class, 'batch_id');
    }

    public function isParsed(): bool
    {
        return $this->parsed_at !== null;
    }
}
