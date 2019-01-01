<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GameRankUpdates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('game_rank_updates', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('game_id');
            $table->integer('rank_old')->nullable();
            $table->integer('rank_new');
            $table->integer('movement', false, false)->nullable();
            $table->decimal('rating_avg', 5, 2);

            $table->timestamps();

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
        Schema::dropIfExists('game_rank_updates');
    }
}
