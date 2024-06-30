<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class WikipediaImportRemoveUnusedFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('crawler_wikipedia_games_list_source', function(Blueprint $table) {
            $table->dropColumn('upcoming_date_eu');
            $table->dropColumn('is_released_eu');
            $table->dropColumn('upcoming_date_us');
            $table->dropColumn('is_released_us');
            $table->dropColumn('upcoming_date_jp');
            $table->dropColumn('is_released_jp');
        });

        Schema::table('feed_item_games', function(Blueprint $table) {
            $table->dropColumn('upcoming_date_eu');
            $table->dropColumn('is_released_eu');
            $table->dropColumn('upcoming_date_us');
            $table->dropColumn('is_released_us');
            $table->dropColumn('upcoming_date_jp');
            $table->dropColumn('is_released_jp');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
