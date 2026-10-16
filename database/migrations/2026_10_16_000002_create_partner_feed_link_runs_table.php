<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartnerFeedLinkRunsTable extends Migration
{
    public function up()
    {
        // One row per import run of a feed link, newest 30 kept per feed (RunRepository prunes
        // on write). The Feed Links table still reads the latest result from the feed link row
        // itself, so the list stays a single query; this table is the evidence behind it.
        // body_excerpt is only kept when a run fails - successful feeds would just fill it with
        // RSS. See improvements #177.
        Schema::create('partner_feed_link_runs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('feed_link_id');
            $table->timestamp('run_at');
            $table->boolean('was_successful');
            $table->string('outcome', 30);
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->string('content_type', 150)->nullable();
            $table->text('message')->nullable();
            $table->unsignedInteger('imported')->nullable();
            $table->unsignedInteger('skipped')->nullable();
            $table->text('body_excerpt')->nullable();

            $table->index(['feed_link_id', 'run_at']);
            $table->foreign('feed_link_id')->references('id')->on('partner_feed_links')->onDelete('cascade');
        });

        // The latest outcome code, for the Feed Links badge. last_run_status keeps the message.
        Schema::table('partner_feed_links', function (Blueprint $table) {
            $table->string('last_run_outcome', 30)->nullable()->after('last_run_status');
        });
    }

    public function down()
    {
        Schema::table('partner_feed_links', function (Blueprint $table) {
            $table->dropColumn('last_run_outcome');
        });

        Schema::dropIfExists('partner_feed_link_runs');
    }
}
