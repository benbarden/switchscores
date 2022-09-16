<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ConvertReviewFeedItemFailures extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $feedItems = \DB::select("SELECT * FROM review_feed_items WHERE process_status <> 'Review created'");

        foreach ($feedItems as $item) {

            $reviewDraft = [
                'site_id' => $item->site_id,
                'game_id' => $item->game_id,
                'item_url' => $item->item_url,
                'item_title' => $item->item_title,
                'parsed_title' => $item->parsed_title,
                'item_date' => $item->item_date,
                'item_rating' => $item->item_rating,
                'parse_status' => $item->parse_status,
                'process_status' => $item->process_status,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ];

            $existingRow = \DB::select("SELECT count(*) AS count FROM review_feed_items WHERE item_url = ?", [$reviewDraft['item_url']]);
            if ($existingRow[0]->count > 0) continue;

            \DB::insert("
                    INSERT INTO review_drafts(
                        source, site_id, user_id, game_id, 
                        item_url, item_title, parsed_title, item_date, item_rating,
                        parse_status, process_status, review_link_id, created_at, updated_at)
                     VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ",
                [
                    'Feed', $reviewDraft['site_id'], null, $reviewDraft['game_id'],
                    $reviewDraft['item_url'], $reviewDraft['item_title'], $reviewDraft['parsed_title'],
                    $reviewDraft['item_date'], $reviewDraft['item_rating'],
                    $reviewDraft['parse_status'], $reviewDraft['process_status'], null,
                    $reviewDraft['created_at'], $reviewDraft['updated_at']
                ]
            );


        }
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
