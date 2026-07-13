<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGameImagesTable extends Migration
{
    public function up()
    {
        Schema::create('game_images', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('game_id')->unique();
            $table->string('square_filename')->nullable();
            $table->string('header_filename')->nullable();
            $table->string('location', 20)->default('legacy')->index();
            $table->timestamp('square_updated_at')->nullable();
            $table->timestamp('header_updated_at')->nullable();
            $table->timestamps();

            $table->foreign('game_id')->references('id')->on('games')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('game_images');
    }
}
