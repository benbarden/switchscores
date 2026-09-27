<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRunTimestampsToPartnerFeedLinksTable extends Migration
{
    public function up()
    {
        Schema::table('partner_feed_links', function (Blueprint $table) {
            // When the importer last ran this feed, and when it last ran it successfully.
            // updated_at can't answer either: it also moves whenever a feed link is edited.
            // Only real imports write these - the staff tester and probe load feeds without
            // importing, and leave them alone. Null means no import has run since this column
            // was added.
            $table->timestamp('last_run_at')->nullable()->after('last_run_status');
            $table->timestamp('last_successful_run_at')->nullable()->after('last_run_at');
        });
    }

    public function down()
    {
        Schema::table('partner_feed_links', function (Blueprint $table) {
            $table->dropColumn(['last_run_at', 'last_successful_run_at']);
        });
    }
}
