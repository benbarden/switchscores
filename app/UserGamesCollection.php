<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserGamesCollection extends Model
{
    const PLAY_STATUS_NOT_STARTED = 'not-started';
    const PLAY_STATUS_NOW_PLAYING = 'now-playing';
    const PLAY_STATUS_PAUSED = 'paused';
    const PLAY_STATUS_ABANDONED = 'abandoned';
    const PLAY_STATUS_COMPLETED = 'completed';

    protected $playStatusNotStarted = [
        'id' => self::PLAY_STATUS_NOT_STARTED,
        'desc' => 'Not started',
        'icon' => 'stop-circle',
        'iconColor' => "#cc0000",
    ];
    protected $playStatusNowPlaying = [
        'id' => UserGamesCollection::PLAY_STATUS_NOW_PLAYING,
        'desc' => 'Now playing',
        'icon' => 'play-circle',
        'iconColor' => "#990099",
    ];
    protected $playStatusPaused = [
        'id' => UserGamesCollection::PLAY_STATUS_PAUSED,
        'desc' => 'Paused - will return',
        'icon' => 'pause-circle',
        'iconColor' => "#FF8C00",
    ];
    protected $playStatusAbandoned = [
        'id' => UserGamesCollection::PLAY_STATUS_ABANDONED,
        'desc' => 'Abandoned',
        'icon' => 'times-circle',
        'iconColor' => "#999999",
    ];
    protected $playStatusCompleted = [
        'id' => UserGamesCollection::PLAY_STATUS_COMPLETED,
        'desc' => 'Completed',
        'icon' => 'check-circle',
        'iconColor' => "#009900",
    ];

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
        'user_id', 'game_id', 'owned_from', 'owned_type',
        'is_started', 'is_ongoing', 'is_complete', 'hours_played', 'play_status'
    ];

    public function game()
    {
        return $this->hasOne('App\Game', 'id', 'game_id');
    }

    public function getPlayStatusItem($status)
    {
        $playStatusItem = [];

        switch ($status) {
            case self::PLAY_STATUS_NOT_STARTED:
                $playStatusItem = $this->playStatusNotStarted;
                break;
            case self::PLAY_STATUS_NOW_PLAYING:
                $playStatusItem = $this->playStatusNowPlaying;
                break;
            case self::PLAY_STATUS_PAUSED:
                $playStatusItem = $this->playStatusPaused;
                break;
            case self::PLAY_STATUS_ABANDONED:
                $playStatusItem = $this->playStatusAbandoned;
                break;
            case self::PLAY_STATUS_COMPLETED:
                $playStatusItem = $this->playStatusCompleted;
                break;
        }

        return $playStatusItem;
    }
}
