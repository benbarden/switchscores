<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsDbUpdate extends Model
{
    const SWITCH_LAUNCH_WEEK_2017 = 9;

    /**
     * @var string
     */
    protected $table = 'news_db_updates';

    /**
     * @var array
     */
    protected $fillable = [
        'news_db_year', 'news_db_week',
        'game_count_standard', 'game_count_low_quality'
    ];
}
