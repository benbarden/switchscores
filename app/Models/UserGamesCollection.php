<?php

namespace App\Models;

use App\Services\GamesCollection\PlayStatus;
use Illuminate\Database\Eloquent\Model;

class UserGamesCollection extends Model
{
    /**
     * @var string
     */
    protected $table = 'user_games_collection';

    /**
     * @var array
     */
    protected $fillable = [
        'user_id', 'game_id', 'owned_from', 'owned_type', 'hours_played', 'play_status'
    ];

    public function game()
    {
        return $this->hasOne('App\Models\Game', 'id', 'game_id');
    }

    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

    public function getPlayStatus($statusId)
    {
        if (!$statusId) return null;

        $servicePlayStatus = new PlayStatus();
        return $servicePlayStatus->generateById($statusId);
    }
}
