<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameDeveloper extends Model
{
    /**
     * @var string
     */
    protected $table = 'game_developers';

    /**
     * @var array
     */
    protected $fillable = [
        'game_id', 'developer_id'
    ];

    public function game()
    {
        return $this->hasOne('App\Models\Game', 'id', 'game_id');
    }

    public function developer()
    {
        return $this->hasOne('App\Models\Partner', 'id', 'developer_id');
    }
}
