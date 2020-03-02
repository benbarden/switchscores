<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FeedItemGame extends Model
{
    const STATUS_PENDING = 101;
    const STATUS_OK_TO_UPDATE = 102;

    const STATUS_COMPLETE = 201;

    const STATUS_NO_UPDATE_NEEDED = 901;
    const STATUS_SKIPPED_BY_USER = 902;
    const STATUS_SKIPPED_BY_GAME_RULES = 903; // future use
    const STATUS_SKIPPED_SUPERSEDED = 904;

    /**
     * @var string
     */
    protected $table = 'feed_item_games';

    /**
     * @var bool
     */
    public $timestamps = true;

    /**
     * @var array
     */
    protected $fillable = [
        'game_id', 'source', 'item_title', 'item_genre', 'item_developers', 'item_publishers',
        'release_date_eu', 'release_date_us', 'release_date_jp', 'status_code', 'status_desc'
    ];

    public function game()
    {
        return $this->hasOne('App\Game', 'id', 'game_id');
    }

    public function setStatusPending()
    {
        $this->status_code = self::STATUS_PENDING;
        $this->status_desc = 'Pending';
    }

    public function setStatusComplete()
    {
        $this->status_code = self::STATUS_COMPLETE;
        $this->status_desc = 'Complete';
    }

    public function setStatusSkippedSuperseded()
    {
        $this->status_code = self::STATUS_SKIPPED_SUPERSEDED;
        $this->status_desc = 'Skipped - superseded';
    }

    public function hasRealUpcomingDate($date)
    {
        if (!$date) return false;
        if (is_null($date)) return false;

        if (in_array($date, ['TBA', 'Unreleased'])) return false;

        return true;
    }
}
