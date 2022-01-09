<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameCollection extends Model
{
    /**
     * @var string
     */
    protected $table = 'game_collections';

    /**
     * @var array
     */
    protected $fillable = [
        'name', 'link_title'
    ];

    public function games()
    {
        return $this->hasMany('App\Models\Game', 'collection_id', 'id');
    }
}
