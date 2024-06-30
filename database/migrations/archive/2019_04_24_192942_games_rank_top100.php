<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GamesRankTop100 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('game_rank_alltime', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('game_rank');
            $table->integer('game_id');

            $table->timestamps();

            $table->index('game_id', 'game_id');
        });

        Schema::create('game_rank_year', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('release_year');
            $table->integer('game_rank');
            $table->integer('game_id');

            $table->timestamps();

            $table->index('release_year', 'release_year');
            $table->index('game_id', 'game_id');
        });

        Schema::create('game_rank_yearmonth', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('release_yearmonth');
            $table->integer('game_rank');
            $table->integer('game_id');

            $table->timestamps();

            $table->index('release_yearmonth', 'release_yearmonth');
            $table->index('game_id', 'game_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('game_rank_alltime');
        Schema::dropIfExists('game_rank_year');
        Schema::dropIfExists('game_rank_yearmonth');
    }
}
