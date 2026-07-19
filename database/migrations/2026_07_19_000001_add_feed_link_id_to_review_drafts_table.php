<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFeedLinkIdToReviewDraftsTable extends Migration
{
    public function up()
    {
        Schema::table('review_drafts', function (Blueprint $table) {
            // Which feed link produced this draft. A site can have more than one feed
            // (e.g. separate Switch 1 and Switch 2 category feeds), each with its own
            // title match rule, so site_id alone can't tell us which rule to apply.
            // Stays nullable: manual and scraper drafts have no feed link at all.
            $table->integer('feed_link_id')->unsigned()->nullable()->after('site_id');
            $table->index('feed_link_id', 'rd_feed_link_id_index');
        });
    }

    public function down()
    {
        Schema::table('review_drafts', function (Blueprint $table) {
            $table->dropIndex('rd_feed_link_id_index');
            $table->dropColumn('feed_link_id');
        });
    }
}
