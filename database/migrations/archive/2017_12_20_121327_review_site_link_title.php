<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ReviewSiteLinkTitle extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('review_sites', function(Blueprint $table) {
            $table->string('link_title');
            $table->index('link_title', 'link_title');
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
            $table->dropColumn('link_title');
        });
    }
}
