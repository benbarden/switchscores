<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewFeedItemTest extends Model
{
    const PARSE_STATUS_AUTO_MATCHED = 'Automatically matched title';
    const PARSE_STATUS_COULD_NOT_LOCATE = 'Could not locate game';

    /**
     * @var string
     */
    protected $table = 'review_feed_items_test';

    /**
     * @var array
     */
    protected $fillable = [
        'site_id', 'game_id', 'item_url', 'item_title', 'item_date', 'item_rating',
        'load_status', 'parse_status', 'parsed', 'process_status', 'processed'
    ];

    public function site()
    {
        return $this->hasOne('App\Models\ReviewSite', 'id', 'site_id');
    }

    public function game()
    {
        return $this->hasOne('App\Models\Game', 'id', 'game_id');
    }

    public function isHistoric()
    {
        // If the review date is older than 30 days from today, it's history!
        if (date('Y-m-d', strtotime('-30 days')) > $this->item_date) {
            return true;
        } else {
            return false;
        }
    }
}
