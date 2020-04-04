<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DataSource extends Model
{
    const SOURCE_SWITCH_ESHOP_UK = 'Switch eShop - UK';
    const SOURCE_NINTENDO_CO_UK = 'Nintendo.co.uk';
    const SOURCE_NINTENDO_COM = 'Nintendo.com';
    const SOURCE_WIKIPEDIA = 'Wikipedia';
    const SOURCE_WHATTOPLAY = 'whattoplay';

    const DSID_SWITCH_ESHOP_UK = 1;
    const DSID_NINTENDO_CO_UK = 2;
    const DSID_NINTENDO_COM = 3;
    const DSID_WIKIPEDIA = 4;
    const DSID_WHATTOPLAY = 5;

    const METHOD_API = 1;
    const METHOD_SCRAPER = 2;
    CONST METHOD_MANUAL = 9;
    // No method = NULL

    /**
     * @var string
     */
    protected $table = 'data_sources';

    /**
     * @var bool
     */
    public $timestamps = true;

    /**
     * @var array
     */
    protected $fillable = [
        'name', 'import_method', 'is_active'
    ];

    public function importMethodDesc()
    {
        $method = '';
        switch ($this->import_method) {
            case self::METHOD_API:     $method = 'API';     break;
            case self::METHOD_SCRAPER: $method = 'Scraper'; break;
            case self::METHOD_MANUAL:  $method = 'Manual';  break;
        }

        return $method;
    }

    public function itemsRaw()
    {
        return $this->hasMany('App\DataSourceRaw', 'source_id', 'id');
    }

    public function itemsParsed()
    {
        return $this->hasMany('App\DataSourceParsed', 'source_id', 'id');
    }

}
