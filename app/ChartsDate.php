<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ChartsDate extends Model
{
    /**
     * @var string
     */
    protected $table = 'charts_dates';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var array
     */
    protected $fillable = [
        'chart_date', 'stats_europe', 'stats_us',
    ];

}
