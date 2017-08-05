<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GenreLinkTitle extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('genres', function(Blueprint $table) {
            $table->string('link_title', '50')->nullable();
            $table->index('link_title', 'link_title');
        });

        DB::update("UPDATE genres SET link_title = 'action-games' WHERE genre = 'Action'");
        DB::update("UPDATE genres SET link_title = 'puzzle-games' WHERE genre = 'Puzzle'");
        DB::update("UPDATE genres SET link_title = 'adventure-games' WHERE genre = 'Adventure'");
        DB::update("UPDATE genres SET link_title = 'platform-games' WHERE genre = 'Platformer'");
        DB::update("UPDATE genres SET link_title = 'racing-games' WHERE genre = 'Racing'");
        DB::update("UPDATE genres SET link_title = 'role-playing-games' WHERE genre = 'RPG'");
        DB::update("UPDATE genres SET link_title = 'party-games' WHERE genre = 'Party'");
        DB::update("UPDATE genres SET link_title = 'arcade-games' WHERE genre = 'Arcade'");
        DB::update("UPDATE genres SET link_title = 'music-games' WHERE genre = 'Music'");
        DB::update("UPDATE genres SET link_title = 'strategy-games' WHERE genre = 'Strategy'");
        DB::update("UPDATE genres SET link_title = 'simulation-games' WHERE genre = 'Simulation'");
        DB::update("UPDATE genres SET link_title = 'fighting-games' WHERE genre = 'Fighting'");
        DB::update("UPDATE genres SET link_title = 'board-games' WHERE genre = 'Board Game'");
        DB::update("UPDATE genres SET link_title = 'shooting-games' WHERE genre = 'Shooter'");
        DB::update("UPDATE genres SET link_title = 'shop-games' WHERE genre = 'Shop'");
        DB::update("UPDATE genres SET link_title = 'lifestyle-games' WHERE genre = 'Lifestyle'");
        DB::update("UPDATE genres SET link_title = 'education-games' WHERE genre = 'Education'");
        DB::update("UPDATE genres SET link_title = 'sports-games' WHERE genre = 'Sports'");
        DB::update("UPDATE genres SET link_title = 'other-games' WHERE genre = 'Other'");
        DB::update("UPDATE genres SET link_title = 'communication-games' WHERE genre = 'Communication'");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('genres', function(Blueprint $table) {
            $table->dropIndex('link_title');
            $table->dropColumn('link_title');
        });
    }
}
