<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMultiplayerFieldsToGames extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('games', function (Blueprint $table) {
            $table->string('multiplayer_mode', 50)->nullable()->after('players');
            $table->boolean('has_online_play')->default(false)->after('multiplayer_mode');
            $table->boolean('has_local_multiplayer')->default(false)->after('has_online_play');
            $table->boolean('play_mode_tv')->default(false)->after('has_local_multiplayer');
            $table->boolean('play_mode_tabletop')->default(false)->after('play_mode_tv');
            $table->boolean('play_mode_handheld')->default(false)->after('play_mode_tabletop');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn([
                'multiplayer_mode',
                'has_online_play',
                'has_local_multiplayer',
                'play_mode_tv',
                'play_mode_tabletop',
                'play_mode_handheld',
            ]);
        });
    }
}
