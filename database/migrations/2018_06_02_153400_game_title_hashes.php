<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GameTitleHashes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('game_title_hashes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title', 150);
            $table->string('title_hash', 64);
            $table->integer('game_id');

            $table->timestamps();

            $table->index('title_hash', 'title_hash');
            $table->index('game_id', 'game_id');
        });

        DB::insert("
            INSERT INTO game_title_hashes(
              title, title_hash, game_id, created_at, updated_at
            )
            SELECT title, md5(title), id, NOW(), NOW()
            FROM games ORDER BY id ASC
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('game_title_hashes');
    }
}
