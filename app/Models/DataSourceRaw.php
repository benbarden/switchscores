<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataSourceRaw extends Model
{
    /**
     * @var string
     */
    protected $table = 'data_source_raw';

    /**
     * @var array
     */
    protected $fillable = [
        'source_id', 'console_id', 'game_id', 'title', 'source_data_json'
    ];
}
