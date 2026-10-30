<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropGameQualityScoresTable extends Migration
{
    public function up()
    {
        // Nothing read these scores, and every value was calculated from other data.
        Schema::dropIfExists('game_quality_scores');
    }

    /**
     * Puts the table back empty.
     */
    public function down()
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
}
