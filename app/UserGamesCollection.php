<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\Services\GamesCollection\PlayStatus;

class UserGamesCollection extends Model
{
    /**
     * @var string
     */
    protected $table = 'user_games_collection';

    /**
     * @var bool
     */
    public $timestamps = true;

    /**
     * @var array
     */
    protected $fillable = [
        'user_id', 'game_id', 'owned_from', 'owned_type', 'hours_played', 'play_status'
    ];

    public function game()
    {
        return $this->hasOne('App\Game', 'id', 'game_id');
    }

    public function getPlayStatus()
    {
        if (!$this->play_status) return null;

        $servicePlayStatus = new PlayStatus();
        return $servicePlayStatus->generateById($this->play_status);
    }
}
