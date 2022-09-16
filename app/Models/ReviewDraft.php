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

    const PROCESS_SUCCESS_REVIEW_CREATED = 'Review created';

    const PROCESS_FAILURE_BUNDLE = 'Bundle';
    const PROCESS_FAILURE_DLC_OR_SPECIAL_EDITION = 'DLC or special edition';
    const PROCESS_FAILURE_DUPLICATE = 'Duplicate';
    const PROCESS_FAILURE_HISTORIC = 'Historic review';
    const PROCESS_FAILURE_MULTIPLE = 'Multiple reviews';
    const PROCESS_FAILURE_NO_SCORE = 'No score';
    const PROCESS_FAILURE_NON_REVIEW_CONTENT = 'Non-review content';
    const PROCESS_FAILURE_NOT_GAME_REVIEW = 'Not a game review';
    const PROCESS_FAILURE_NOT_IN_DB = 'Not in database';
    const PROCESS_FAILURE_PAGE_NOT_FOUND = 'Page not found';
    const PROCESS_FAILURE_REVIEW_FOR_ANOTHER_PLATFORM = 'Review for another platform';
    const PROCESS_FAILURE_REVIEW_PREDATES_SWITCH_VERSION = 'Review pre-dates Switch version';

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
        return $this->hasOne('App\Models\ReviewSite', 'id', 'site_id');
    }

    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

    public function game()
    {
        return $this->hasOne('App\Models\Game', 'id', 'game_id');
    }

    public function reviewLink()
    {
        return $this->hasOne('App\Models\ReviewLink', 'id', 'review_link_id');
    }

    public function isHistoric()
    {
        // If the content is older than 30 days from today, it's history!
        if (date('Y-m-d', strtotime('-30 days')) > $this->item_date) {
            return true;
        } else {
            return false;
        }
    }
}
