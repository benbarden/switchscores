<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GameDescriptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('game_descriptions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('game_id');
            $table->integer('user_id')->nullable();
            $table->text('description')->nullable();
            $table->tinyInteger('status');
            $table->timestamps();

            $table->index('status', 'status');
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
        Schema::drop('game_descriptions');
    }
}
