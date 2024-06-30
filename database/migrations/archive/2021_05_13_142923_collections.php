<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Collections extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('game_collections', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100);
            $table->string('link_title', 100);
            $table->timestamps();

            $table->index('link_title', 'link_title');
        });

        Schema::table('games', function(Blueprint $table) {
            $table->integer('collection_id')->after('series_id')->nullable();
            $table->index('collection_id', 'collection_id');
        });

        DB::insert("
            INSERT INTO game_collections(id, name, link_title, created_at, updated_at)
            VALUES(1, 'ACA NeoGeo', 'aca-neogeo', NOW(), NOW())
        ");
        DB::insert("
            INSERT INTO game_collections(id, name, link_title, created_at, updated_at)
            VALUES(2, 'Arcade Archives', 'arcade-archives', NOW(), NOW())
        ");
        DB::insert("
            INSERT INTO game_collections(id, name, link_title, created_at, updated_at)
            VALUES(3, 'Johnny Turbo\'s Arcade', 'johnny-turbos-arcade', NOW(), NOW())
        ");
        DB::insert("
            INSERT INTO game_collections(id, name, link_title, created_at, updated_at)
            VALUES(4, 'Lego', 'lego', NOW(), NOW())
        ");
        DB::insert("
            INSERT INTO game_collections(id, name, link_title, created_at, updated_at)
            VALUES(5, 'Sega Ages', 'sega-ages', NOW(), NOW())
        ");
        DB::insert("
            INSERT INTO game_collections(id, name, link_title, created_at, updated_at)
            VALUES(6, 'G-Mode Archives', 'g-mode-archives', NOW(), NOW())
        ");
        DB::insert("
            INSERT INTO game_collections(id, name, link_title, created_at, updated_at)
            VALUES(7, 'Pixel Game Maker', 'pixel-game-maker', NOW(), NOW())
        ");
        DB::insert("
            INSERT INTO game_collections(id, name, link_title, created_at, updated_at)
            VALUES(8, 'Konami Anniversary', 'konami-anniversary', NOW(), NOW())
        ");
        DB::insert("
            INSERT INTO game_collections(id, name, link_title, created_at, updated_at)
            VALUES(9, 'NeoGeo Pocket', 'neogeo-pocket', NOW(), NOW())
        ");

        DB::update("UPDATE games SET collection_id = 1, series_id = NULL WHERE title LIKE 'ACA NeoGeo%'");
        DB::update("UPDATE games SET collection_id = 2, series_id = NULL WHERE title LIKE 'Arcade Archives%'");
        DB::update("UPDATE games SET collection_id = 3, series_id = NULL WHERE title LIKE 'Johnny Turbo\'s Arcade%'");
        DB::update("UPDATE games SET collection_id = 4, series_id = NULL WHERE title LIKE 'Lego%'");
        DB::update("UPDATE games SET collection_id = 5, series_id = NULL WHERE title LIKE 'Sega Ages%'");
        DB::update("UPDATE games SET collection_id = 6, series_id = NULL WHERE title LIKE 'G-Mode Archives%'");
        DB::update("UPDATE games SET collection_id = 7, series_id = NULL WHERE title LIKE 'Pixel Game Maker%'");
        DB::update("UPDATE games SET collection_id = 8, series_id = NULL WHERE title LIKE 'Konami Anniversary%'");
        DB::update("UPDATE games SET collection_id = 9, series_id = NULL WHERE title LIKE 'NeoGeo Pocket%'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('games', function(Blueprint $table) {
            $table->dropColumn('collection_id');
        });

        Schema::dropIfExists('game_collections');
    }
}
