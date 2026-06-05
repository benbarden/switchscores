<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeeklyBatchExclusion extends Model
{
    protected $table = 'weekly_batch_exclusions';

    protected $fillable = ['title', 'console', 'notes'];
}
