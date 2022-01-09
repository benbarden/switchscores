<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataSourceIgnore extends Model
{
    /**
     * @var string
     */
    protected $table = 'data_source_ignore';

    /**
     * @var array
     */
    protected $fillable = [
        'source_id', 'link_id', 'title'
    ];

}
