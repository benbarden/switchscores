<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class NewsDbUpdates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('news_db_updates', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('news_db_year');
            $table->integer('news_db_week');
            $table->integer('game_count_standard')->default(0);
            $table->integer('game_count_low_quality')->default(0);

            $table->timestamps();

            $table->unique(['news_db_year', 'news_db_week']);
            $table->index('news_db_year', 'news_db_year');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('news_db_updates');
    }
}
