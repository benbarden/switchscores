<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DataSourceIgnore extends Model
{
    /**
     * @var string
     */
    protected $table = 'data_source_ignore';

    /**
     * @var bool
     */
    public $timestamps = true;

    /**
     * @var array
     */
    protected $fillable = [
        'source_id', 'link_id', 'title'
    ];

}
