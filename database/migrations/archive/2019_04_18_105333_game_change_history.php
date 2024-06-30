<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GameChangeHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('game_change_history', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('game_id');
            $table->string('affected_table_name', 100);
            $table->string('source', 30);
            $table->string('change_type', 20);
            $table->text('data_old')->nullable();
            $table->text('data_new')->nullable();
            $table->text('data_changed')->nullable();
            $table->integer('user_id')->nullable();

            $table->timestamps();

            $table->index('game_id', 'game_id');
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
        Schema::dropIfExists('game_change_history');
    }
}
