<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataSourcePriceHistory extends Model
{
    protected $table = 'data_source_price_history';

    public $timestamps = false;

    protected $fillable = [
        'nsuid',
        'old_regular_price', 'new_regular_price',
        'old_discount_price', 'new_discount_price',
        'recorded_at',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
    ];
}
