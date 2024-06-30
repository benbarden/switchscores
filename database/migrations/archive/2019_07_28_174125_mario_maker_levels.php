<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MarioMakerLevels extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mario_maker_levels', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('level_code', 11);
            $table->integer('game_style_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('status');
            $table->timestamps();

            $table->index('user_id', 'user_id');
            $table->unique('level_code', 'level_code');
            $table->index('game_style_id', 'game_style_id');
            $table->index('status', 'status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mario_maker_levels');
    }
}
