<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewDraft extends Model
{
    const SOURCE_MANUAL = 'Manual';
    const SOURCE_FEED = 'Feed';
    const SOURCE_SCRAPER = 'Scraper';

    const PARSE_STATUS_AUTO_MATCHED = 'Automatically matched title';
    const PARSE_STATUS_COULD_NOT_LOCATE = 'Could not locate game';

    /**
     * @var string
     */
    protected $table = 'review_drafts';

    /**
     * @var array
     */
    protected $fillable = [
        'source', 'site_id', 'user_id', 'game_id', 'item_url', 'item_title', 'parsed_title',
        'item_date', 'item_rating', 'parse_status', 'process_status', 'review_link_id'
    ];

    public function site()
    {
        return $this->hasOne('App\Partner', 'id', 'site_id');
    }

    public function user()
    {
        return $this->hasOne('App\User', 'id', 'user_id');
    }

    public function game()
    {
        return $this->hasOne('App\Game', 'id', 'game_id');
    }

    public function reviewLink()
    {
        return $this->hasOne('App\ReviewLink', 'id', 'review_link_id');
    }
}
