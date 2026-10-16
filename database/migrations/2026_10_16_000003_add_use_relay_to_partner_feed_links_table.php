<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUseRelayToPartnerFeedLinksTable extends Migration
{
    public function up()
    {
        Schema::table('partner_feed_links', function (Blueprint $table) {
            // Fetch this feed through the egress relay (a Cloudflare Worker) instead of directly.
            // For sites whose bot protection refuses the server's datacentre IP: six feeds did on
            // 2026-10-16, and all six came through the relay as full RSS. Off by default, so only
            // blocked feeds take the extra hop. Set per feed on the staff edit form (#174).
            $table->boolean('use_relay')->default(false)->after('allow_historic_content');
        });
    }

    public function down()
    {
        Schema::table('partner_feed_links', function (Blueprint $table) {
            $table->dropColumn('use_relay');
        });
    }
}
