<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ReviewFeedItemsNormaliseProcessStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            update review_feed_items
            set process_status = 'No score'
            where process_status in (
                'No rating', 'No ratings', 'No review score', 
                'No score', 'No score (yet)', 'No score awarded', 
                'No scores'
            );
        ");

        DB::statement("
            update review_feed_items
            set process_status = 'DLC or special edition'
            where process_status in (
                'DLC', 'DLC - not a standalone game', 'DLC / special edition', 'DLC review', 'DLC?', 
                'Edition review', 'Episode review', 'Not a full game', 'Not a standalone game', 
                'Physical version of an existing game', 'Single episode review?', 'Skip; seems like DLC'
            );
        ");

        DB::statement("
            update review_feed_items
            set process_status = 'Page not found'
            where process_status in ('404', 'Removed from site', 'Review was deleted');
        ");

        DB::statement("
            update review_feed_items
            set process_status = 'Review for another platform'
            where process_status in (
                '3DS', '3DS game', '3DS review', 'Mobile game', 'NES / Switch online game review', 
                'Not a Switch game review', 'Not a Switch review', 'Old Wii U review - not Switch', 
                'Online game review', 'PS4', 'Skipping as it\'s on PC', 'SNES', 'Switch online review', 
                'Xbox', 'Xbox review'
            );
        ");

        DB::statement("
            update review_feed_items
            set process_status = 'Multiple reviews'
            where process_status in (
                'Combined review', 'Multiple reviews - to split up', 'Multiple reviews - will add manually', 
                'Round-up', 'Split up', 'Splitting up', 'To add as separate reviews', 'To add separately', 
                'To be split up', 'To split up', 'Two reviews in one - I\'ve done this one manually'
            );
        ");

        DB::statement("
            update review_feed_items
            set process_status = 'Not a game review'
            where process_status in (
                'Not a game', 'Not a game!', 'Not a review', 'Not a review, and not on Switch', 
                'Not a Switch game', 'Preview'
            );
        ");

        DB::statement("
            update review_feed_items
            set process_status = 'Bundle'
            where process_status in (
                'Bundle - not in db', 'Not in db - bundle', 'Not on db', 
                'Trilogy pack - not in db', 'Trilogy/Bundle - not in db'
            );
        ");

        DB::statement("
            update review_feed_items
            set process_status = 'Historic review'
            where process_status in (
                'Historic re-import', 'Historic re-post', 'Old review', 'Old review, not yet on Switch', 
                'Pre Switch launch', 'Pre-Switch launch'
            );
        ");

        DB::statement("
            update review_feed_items
            set process_status = 'Review pre-dates Switch version'
            where process_status in (
                'Not on Switch (yet)', 'Not on Switch yet', 'Not out on Switch', 
                'Not out on Switch yet', 'Switch version not out yet'
            );
        ");

        DB::statement("
            update review_feed_items
            set process_status = 'Duplicate'
            where process_status in ('Added twice', 'Already imported manually');
        ");

        DB::statement("
            update review_feed_items
            set process_status = 'Non-review content'
            where process_status in ('List, not a review');
        ");

        DB::statement("
            update review_feed_items
            set process_status = 'Not in database'
            where process_status in ('Not in db', 'Not out in UK', 'Part of a 2 game pack');
        ");

        DB::statement("
            update review_feed_items
            set process_status = 'Review for another platform'
            where process_status in ('Not on Switch', 'Not Switch');
        ");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
