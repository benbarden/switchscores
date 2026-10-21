<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGameGroupsTable extends Migration
{
    public function up()
    {
        // Games sharing a group are the same game on different consoles (Switch 1 / Switch 2).
        // A group rather than a pointer to the counterpart: nothing to keep in step, and
        // group-level notes have a home. match_type is 'identical' or 'enhanced' (a Switch 2
        // Edition with material differences). See improvements #44 and docs/tasks/044-game-groups.md.
        Schema::create('game_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('match_type', 20);
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::table('games', function (Blueprint $table) {
            $table->unsignedBigInteger('game_group_id')->nullable()->after('console_id');
            $table->foreign('game_group_id')->references('id')->on('game_groups')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropForeign(['game_group_id']);
            $table->dropColumn('game_group_id');
        });

        Schema::dropIfExists('game_groups');
    }
}
