<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddConsoleIdToPartnerFeedLinksTable extends Migration
{
    public function up()
    {
        Schema::table('partner_feed_links', function (Blueprint $table) {
            // The console a feed's reviews are for, when the partner splits feeds by console
            // (e.g. separate Switch 1 and Switch 2 category feeds). Title hashes are unique per
            // title + console, so a title on both consoles can only be matched to the right
            // game if the feed says which console it covers. Null means the feed is not
            // console-specific.
            $table->integer('console_id')->nullable()->after('site_id');
        });
    }

    public function down()
    {
        Schema::table('partner_feed_links', function (Blueprint $table) {
            $table->dropColumn('console_id');
        });
    }
}
