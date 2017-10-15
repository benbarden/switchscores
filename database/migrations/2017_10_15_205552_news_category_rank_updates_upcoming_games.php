<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NewsCategoryRankUpdatesUpcomingGames extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::insert("
            INSERT INTO news_categories(name, link_name, created_at, updated_at)
            VALUES('Rank updates', 'rank-updates', now(), now())
        ");
        DB::insert("
            INSERT INTO news_categories(name, link_name, created_at, updated_at)
            VALUES('Upcoming games', 'upcoming-games', now(), now())
        ");
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
