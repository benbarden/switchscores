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
        'release_date_eu', 'upcoming_date_eu', 'is_released_eu',
        'release_date_us', 'upcoming_date_us', 'is_released_us',
        'release_date_jp', 'upcoming_date_jp', 'is_released_jp',
        'status_code', 'status_desc'
    ];

    public function game()
    {
        return $this->hasOne('App\Game', 'id', 'game_id');
    }

    public function setStatusComplete()
    {
        $this->status_code = self::STATUS_COMPLETE;
        $this->status_desc = 'Complete';
    }
}
