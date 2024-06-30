<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ReviewLinkType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('review_links', function(Blueprint $table) {
            $table->string('review_type');
            $table->index('review_type', 'review_type');
        });

        DB::update("UPDATE review_links SET review_type = 'Manual'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('review_links', function(Blueprint $table) {
            $table->dropColumn('review_type');
        });
    }
}
