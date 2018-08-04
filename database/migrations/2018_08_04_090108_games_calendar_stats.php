<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GamesCalendarStats extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('game_calendar_stats', function (Blueprint $table) {
            $table->increments('id');
            $table->string('region', 2);
            $table->string('month_name', 10);
            $table->integer('released_count');

            $table->timestamps();

            $table->index('region', 'region');
            $table->index('month_name', 'month_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('game_calendar_stats');
    }
}
