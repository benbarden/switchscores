<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTitleMatchRateToPartnerFeedLinksTable extends Migration
{
    public function up()
    {
        Schema::table('partner_feed_links', function (Blueprint $table) {
            // Percentage of the most recent sample of drafts whose title parsed and matched a
            // game. Stored rather than derived: computing it live would mean a game lookup per
            // title per feed on every page view. Recalculated on save from the rule tester and
            // by the PartnerUpdateTitleMatchRates command.
            $table->tinyInteger('title_match_rate')->unsigned()->nullable()->after('title_match_rule_index');
            $table->timestamp('title_match_rate_at')->nullable()->after('title_match_rate');
        });
    }

    public function down()
    {
        Schema::table('partner_feed_links', function (Blueprint $table) {
            $table->dropColumn(['title_match_rate', 'title_match_rate_at']);
        });
    }
}
