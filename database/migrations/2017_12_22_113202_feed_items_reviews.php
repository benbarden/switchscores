<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FeedItemsReviews extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('feed_item_reviews', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('site_id');
            $table->integer('game_id')->nullable();
            $table->string('item_url', 200);
            $table->string('item_title', 200);
            $table->dateTime('item_date')->nullable();
            $table->integer('item_rating')->nullable();
            $table->text('load_status')->nullable();
            $table->text('parse_status')->nullable();
            $table->tinyInteger('processed')->nullable();

            $table->timestamps();

            $table->index('site_id', 'site_id');
            $table->index('game_id', 'game_id');
            $table->index('processed', 'processed');

            $table->index('item_url', 'item_url');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('feed_item_reviews');
    }
}
