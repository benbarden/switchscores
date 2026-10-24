<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeeklyPasteMissingCheck extends Model
{
    protected $table = 'weekly_paste_missing_checks';

    protected $fillable = ['game_id', 'checked_on'];
}
