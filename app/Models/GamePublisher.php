<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GamePublisher extends Model
{
    /**
     * @var string
     */
    protected $table = 'game_publishers';

    /**
     * @var array
     */
    protected $fillable = [
        'game_id', 'publisher_id'
    ];

    public function game()
    {
        return $this->hasOne('App\Game', 'id', 'game_id');
    }

    public function publisher()
    {
        return $this->hasOne('App\Models\Partner', 'id', 'publisher_id');
    }
}
