<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GameTag extends Model
{
    /**
     * @var string
     */
    protected $table = 'game_tags';

    /**
     * @var array
     */
    protected $fillable = [
        'game_id', 'tag_id'
    ];

    public function game()
    {
        return $this->hasOne('App\Game', 'id', 'game_id');
    }

    public function tag()
    {
        return $this->hasOne('App\Tag', 'id', 'tag_id');
    }
}
