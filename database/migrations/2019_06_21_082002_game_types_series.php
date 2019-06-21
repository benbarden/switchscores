<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GameTypesSeries extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('game_primary_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('primary_type', 100);
            $table->string('link_title', 100);
            $table->timestamps();

            $table->index('link_title', 'link_title');
        });

        Schema::create('game_series', function (Blueprint $table) {
            $table->increments('id');
            $table->string('series', 100);
            $table->string('link_title', 100);
            $table->timestamps();

            $table->index('link_title', 'link_title');
        });

        Schema::table('games', function(Blueprint $table) {
            $table->integer('primary_type_id')->nullable();
            $table->integer('series_id')->nullable();

            $table->index('primary_type_id', 'primary_type_id');
            $table->index('series_id', 'series_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('games', function(Blueprint $table) {
            $table->dropColumn('primary_type_id');
            $table->dropColumn('series_id');
        });

        Schema::dropIfExists('game_primary_types');
        Schema::dropIfExists('game_series');
    }
}
