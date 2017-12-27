<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FeedItemReview extends Model
{
    /**
     * @var string
     */
    protected $table = 'feed_item_reviews';

    /**
     * @var bool
     */
    public $timestamps = true;

    /**
     * @var array
     */
    protected $fillable = [
        'site_id', 'game_id', 'item_url', 'item_title', 'item_date', 'item_rating',
        'load_status', 'parse_status', 'parsed', 'process_status', 'processed'
    ];

    public function site()
    {
        return $this->hasOne('App\ReviewSite', 'id', 'site_id');
    }

    public function game()
    {
        return $this->hasOne('App\Game', 'id', 'game_id');
    }
}
