<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DbEditsGames extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('db_edits_games', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('game_id');
            $table->string('data_to_update', 50);
            $table->text('current_data')->nullable();
            $table->text('new_data');
            $table->integer('status');
            $table->integer('change_history_id')->nullable();
            $table->integer('point_transaction_id')->nullable();
            $table->timestamps();

            $table->index('user_id', 'user_id');
            $table->index('game_id', 'game_id');
            $table->index('status', 'status');
            $table->index('change_history_id', 'change_history_id');
            $table->index('point_transaction_id', 'point_transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('db_edits_games');
    }
}
