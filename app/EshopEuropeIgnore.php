<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EshopEuropeIgnore extends Model
{
    /**
     * @var string
     */
    protected $table = 'eshop_europe_ignore';

    /**
     * @var bool
     */
    public $timestamps = true;

    /**
     * @var array
     */
    protected $fillable = ['fs_id'];

    public function eshopEuropeGame()
    {
        return $this->hasOne('App\EshopEuropeGame', 'fs_id', 'fs_id');
    }

}