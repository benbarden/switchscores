<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataSourceParsed extends Model
{
    /**
     * @var string
     */
    protected $table = 'data_source_parsed';

    /**
     * @var array
     */
    protected $fillable = [
        'source_id', 'game_id', 'link_id', 'title',
        'release_date_eu', 'release_date_us', 'release_date_jp',
        'price_standard', 'price_discounted', 'price_discount_pc',
        'developers', 'publishers', 'genres_json', 'players', 'url',
        'image_square', 'image_header',
        'has_physical_version', 'has_dlc', 'has_demo'
    ];

    public function isSourceNintendoCoUk()
    {
        return $this->source_id == DataSource::DSID_NINTENDO_CO_UK;
    }

    public function isSourceWikipedia()
    {
        return $this->source_id == DataSource::DSID_WIKIPEDIA;
    }
}
