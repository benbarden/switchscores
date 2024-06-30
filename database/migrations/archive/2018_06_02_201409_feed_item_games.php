<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FeedItemGames extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('feed_item_games', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('game_id')->nullable();
            $table->string('source', 50);
            $table->text('item_title');
            $table->text('item_genre')->nullable();
            $table->text('item_developers')->nullable();
            $table->text('item_publishers')->nullable();
            $table->date('release_date_eu')->nullable();
            $table->string('upcoming_date_eu', 50)->nullable();
            $table->tinyInteger('is_released_eu')->nullable();
            $table->date('release_date_us')->nullable();
            $table->string('upcoming_date_us', 50)->nullable();
            $table->tinyInteger('is_released_us')->nullable();
            $table->date('release_date_jp')->nullable();
            $table->string('upcoming_date_jp', 50)->nullable();
            $table->tinyInteger('is_released_jp')->nullable();

            $table->text('modified_fields')->nullable();

            $table->integer('status_code');
            $table->string('status_desc', 50);

            $table->timestamps();

            $table->index('game_id', 'game_id');
            $table->index('status_code', 'status_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('feed_item_games');
    }
}
