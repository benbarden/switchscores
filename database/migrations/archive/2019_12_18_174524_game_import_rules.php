<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GameImportRules extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('game_import_rules_eshop', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('game_id');
            $table->tinyInteger('ignore_publishers');
            $table->tinyInteger('ignore_europe_dates');
            $table->tinyInteger('ignore_price');
            $table->tinyInteger('ignore_players');
            $table->tinyInteger('ignore_genres');
            $table->timestamps();

            $table->index('game_id', 'game_id');
        });

        Schema::create('game_import_rules_wikipedia', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('game_id');
            $table->tinyInteger('ignore_developers');
            $table->tinyInteger('ignore_publishers');
            $table->tinyInteger('ignore_europe_dates');
            $table->tinyInteger('ignore_us_dates');
            $table->tinyInteger('ignore_jp_dates');
            $table->timestamps();

            $table->index('game_id', 'game_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('game_import_rules_eshop');
        Schema::drop('game_import_rules_wikipedia');
    }
}
