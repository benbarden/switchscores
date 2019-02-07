<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ReviewSiteFeedUrlPrefix extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('review_sites', function(Blueprint $table) {
            $table->string('feed_url_prefix', 50)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('review_sites', function(Blueprint $table) {
            $table->dropColumn('feed_url_prefix');
        });
    }
}
