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
     * @var bool
     */
    public $timestamps = true;

    /**
     * @var array
     */
    protected $fillable = [
        'region', 'month_name', 'released_count',
    ];
}
