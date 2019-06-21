<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GamePrimaryType extends Model
{
    /**
     * @var string
     */
    protected $table = 'game_primary_types';

    /**
     * @var bool
     */
    public $timestamps = true;

    /**
     * @var array
     */
    protected $fillable = [
        'primary_type', 'link_title'
    ];

    public function games()
    {
        return $this->hasMany('App\Game', 'primary_type_id', 'id');
    }
}
