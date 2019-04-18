<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SiteAlert extends Model
{
    const TYPE_ERROR = 1;
    const TYPE_WARNING = 2;
    const TYPE_INFO = 3;

    /**
     * @var string
     */
    protected $table = 'site_alerts';

    /**
     * @var bool
     */
    public $timestamps = true;

    /**
     * @var array
     */
    protected $fillable = [
        'type', 'source', 'detail'
    ];
}
