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
        'source_id', 'game_id', 'link_id', 'title',
        'release_date_eu', 'price_standard', 'price_discounted', 'price_discount_pc',
        'developers', 'publishers', 'genres_json', 'players', 'url',
        'image_square', 'image_header'
    ];
}
