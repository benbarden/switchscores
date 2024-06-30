<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GamesCollection extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_games_collection', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('game_id');
            $table->date('owned_from')->nullable();
            $table->string('owned_type', 20)->nullable();
            $table->tinyInteger('is_started')->nullable();
            $table->tinyInteger('is_ongoing')->nullable();
            $table->tinyInteger('is_complete')->nullable();
            $table->integer('hours_played')->nullable();

            $table->timestamps();

            $table->index('user_id', 'user_id');
            $table->index('game_id', 'game_id');
        });

        DB::insert('
            INSERT INTO user_games_collection(user_id, game_id, created_at, updated_at)
            select ul.user_id, uli.game_id, NOW(), NOW() from user_lists ul
            join user_list_items uli on ul.id = uli.list_id
            where ul.list_type = 1
            order by ul.user_id asc
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_games_collection');
    }
}
