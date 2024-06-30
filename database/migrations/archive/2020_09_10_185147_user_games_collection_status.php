<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UserGamesCollectionStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_games_collection', function(Blueprint $table) {
            $table->string('play_status', 30)->nullable();
        });

        DB::update('
            UPDATE user_games_collection SET play_status = "not-started" WHERE is_started = 0 AND is_ongoing = 0 AND is_complete = 0;
        ');
        DB::update('
            UPDATE user_games_collection SET play_status = "now-playing" WHERE is_ongoing = 1 AND is_complete = 0;
        ');
        DB::update('
            UPDATE user_games_collection SET play_status = "paused" WHERE is_started = 1 AND is_ongoing = 0 AND is_complete = 0;
        ');
        DB::update('
            UPDATE user_games_collection SET play_status = "completed" WHERE is_complete = 1;
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_games_collection', function(Blueprint $table) {
            $table->dropColumn('play_status');
        });
    }
}
