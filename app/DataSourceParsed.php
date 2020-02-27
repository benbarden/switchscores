<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DataSourceParsed extends Model
{
    /**
     * @var string
     */
    protected $table = 'data_source_parsed';

    /**
     * @var bool
     */
    public $timestamps = true;

    /**
     * @var array
     */
    protected $fillable = [
        'source_id', 'game_id', 'developers', 'publishers'
    ];
}
