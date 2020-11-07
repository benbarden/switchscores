<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GameQualityScores extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('game_quality_scores', function (Blueprint $table) {
            $table->integer('game_id');
            $table->unique('game_id');
            $table->decimal('quality_score', 6, 2)->default(0.00);
            $table->tinyInteger('has_category')->default(0);
            $table->tinyInteger('has_developers')->default(0);
            $table->tinyInteger('has_publishers')->default(0);
            $table->tinyInteger('has_players')->default(0);
            $table->tinyInteger('has_price')->default(0);
            $table->tinyInteger('no_conflict_nintendo_eu_release_date')->default(0);
            $table->tinyInteger('no_conflict_nintendo_price')->default(0);
            $table->tinyInteger('no_conflict_nintendo_players')->default(0);
            $table->tinyInteger('no_conflict_nintendo_publishers')->default(0);
            $table->tinyInteger('no_conflict_nintendo_genre')->default(0);
            $table->tinyInteger('no_conflict_wikipedia_eu_release_date')->default(0);
            $table->tinyInteger('no_conflict_wikipedia_us_release_date')->default(0);
            $table->tinyInteger('no_conflict_wikipedia_jp_release_date')->default(0);
            $table->tinyInteger('no_conflict_wikipedia_developers')->default(0);
            $table->tinyInteger('no_conflict_wikipedia_publishers')->default(0);
            $table->tinyInteger('no_conflict_wikipedia_genre')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('game_quality_scores');
    }
}
