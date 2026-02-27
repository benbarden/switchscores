<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGameScrapedDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('game_scraped_data', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('game_id')->unsigned();
            $table->string('players_local', 20)->nullable();
            $table->string('players_wireless', 20)->nullable();
            $table->string('players_online', 20)->nullable();
            $table->string('multiplayer_mode', 50)->nullable();
            $table->json('features_json')->nullable();
            $table->timestamp('scraped_at')->nullable();
            $table->timestamps();

            $table->unique('game_id');
            $table->foreign('game_id')->references('id')->on('games')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('game_scraped_data');
    }
}
