<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class WikipediaChanges extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('crawler_wikipedia_games_list_source');
        Schema::dropIfExists('crawler_wikipedia_games_list_staging');

        // Source data - used so we can see the entirety of the latest import
        Schema::create('crawler_wikipedia_games_list_source', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title', 150);
            $table->string('genres', 150)->nullable();
            $table->text('developers')->nullable();
            $table->text('publishers')->nullable();
            $table->date('release_date_eu')->nullable();
            $table->string('upcoming_date_eu', 50)->nullable();
            $table->tinyInteger('is_released_eu');
            $table->date('release_date_us')->nullable();
            $table->string('upcoming_date_us', 50)->nullable();
            $table->tinyInteger('is_released_us');
            $table->date('release_date_jp')->nullable();
            $table->string('upcoming_date_jp', 50)->nullable();
            $table->tinyInteger('is_released_jp');

            $table->timestamps();

            $table->index('title', 'title');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('crawler_wikipedia_games_list_source');
        Schema::dropIfExists('crawler_wikipedia_games_list_staging');
    }
}
