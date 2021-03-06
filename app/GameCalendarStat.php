<?php

namespace App;

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
        'month_name', 'released_count',
    ];
}
