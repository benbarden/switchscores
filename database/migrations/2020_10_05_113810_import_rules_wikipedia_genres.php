<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ImportRulesWikipediaGenres extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('game_import_rules_wikipedia', function(Blueprint $table) {
            $table->tinyInteger('ignore_genres');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('game_import_rules_wikipedia', function(Blueprint $table) {
            $table->dropColumn('ignore_genres');
        });
    }
}
