<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PartnerReviews extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('partner_reviews', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('site_id');
            $table->integer('game_id');
            $table->string('item_url', 200);
            $table->date('item_date');
            $table->decimal('item_rating', 4, 1);
            $table->integer('item_status');
            $table->integer('review_link_id')->nullable();

            $table->timestamps();

            $table->index('user_id', 'user_id');
            $table->index('site_id', 'site_id');
            $table->index('game_id', 'game_id');
            $table->index('review_link_id', 'review_link_id');
            $table->index('item_status', 'item_status');
        });

        Schema::table('users', function(Blueprint $table) {
            $table->integer('site_id');
            $table->index('site_id', 'site_id');
        });

        Schema::table('review_links', function(Blueprint $table) {
            $table->integer('user_id')->nullable();
            $table->index('user_id', 'user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('partner_reviews');
        Schema::table('users', function(Blueprint $table) {
            $table->dropColumn('site_id');
        });
        Schema::table('review_links', function(Blueprint $table) {
            $table->dropColumn('user_id');
        });
    }
}
