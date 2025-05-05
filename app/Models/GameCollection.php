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

    public function gamesSwitch1()
    {
        return $this->hasMany('App\Models\Game', 'collection_id', 'id')
            ->where('console_id', Console::ID_SWITCH_1);
    }

    public function gamesSwitch2()
    {
        return $this->hasMany('App\Models\Game', 'collection_id', 'id')
            ->where('console_id', Console::ID_SWITCH_2);
    }
}
