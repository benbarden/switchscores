<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DataSourceRaw extends Model
{
    /**
     * @var string
     */
    protected $table = 'data_source_raw';

    /**
     * @var bool
     */
    public $timestamps = true;

    /**
     * @var array
     */
    protected $fillable = [
        'source_id', 'game_id', 'title', 'source_data_json'
    ];
}
