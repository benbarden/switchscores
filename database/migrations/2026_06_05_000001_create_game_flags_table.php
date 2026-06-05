<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGameFlagsTable extends Migration
{
    public function up()
    {
        Schema::create('game_flags', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('game_id');
            $table->string('flag', 100);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['game_id', 'flag']);
            $table->foreign('game_id')->references('id')->on('games')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('game_flags');
    }
}
