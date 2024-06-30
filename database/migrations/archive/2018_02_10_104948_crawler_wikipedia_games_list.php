<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CrawlerWikipediaGamesList extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Source data - used so we can see the entirety of the latest import
        Schema::create('crawler_wikipedia_games_list_source', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title', 150);
            $table->string('genres', 150);
            $table->text('developers');
            $table->text('publishers');
            $table->string('exclusive', 20);
            $table->string('release_date_jp', 50);
            $table->string('release_date_na', 50);
            $table->string('release_date_pal', 50);

            $table->timestamps();

            $table->index('title', 'title');
        });

        // Staging data
        // Contains WOS game id.
        // This table is used to generate the list of changes to show in Admin.
        Schema::create('crawler_wikipedia_games_list_staging', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title', 150);
            $table->string('genres', 150);
            $table->text('developers');
            $table->text('publishers');
            $table->string('exclusive', 20);
            $table->string('release_date_jp', 50);
            $table->string('release_date_na', 50);
            $table->string('release_date_pal', 50);

            $table->integer('game_id')->nullable();

            $table->timestamps();

            $table->index('title', 'title');
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
        Schema::dropIfExists('crawler_wikipedia_games_list_source');
        Schema::dropIfExists('crawler_wikipedia_games_list_staging');
    }
}
