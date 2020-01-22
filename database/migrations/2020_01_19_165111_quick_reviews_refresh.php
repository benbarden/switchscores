<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class QuickReviewsRefresh extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quick_reviews', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('game_id');
            $table->integer('site_id')->nullable();
            $table->decimal('review_score', 4, 1)->nullable();
            $table->text('review_body')->nullable();
            $table->integer('item_status');

            $table->timestamps();

            $table->index('game_id', 'game_id');
            $table->index('user_id', 'user_id');
            $table->index('item_status', 'item_status');
        });

        DB::statement('
            INSERT INTO quick_reviews(user_id, game_id, review_score, review_body, item_status, created_at, updated_at)
            SELECT user_id, game_id, review_score, review_body, item_status, created_at, updated_at
            FROM review_user
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quick_reviews');
    }
}
