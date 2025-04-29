<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameCalendarStat extends Model
{
    /**
     * @var string
     */
    protected $table = 'game_calendar_stats';

    /**
     * @var array
     */
    protected $fillable = [
        'month_name', 'console_id', 'released_count',
    ];
}
