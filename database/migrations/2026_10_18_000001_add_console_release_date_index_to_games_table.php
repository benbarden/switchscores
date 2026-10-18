<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddConsoleReleaseDateIndexToGamesTable extends Migration
{
    public function up()
    {
        Schema::table('games', function (Blueprint $table) {
            // Browse-by-month runs eight queries per page, all on one console's games in one
            // month. They filtered with YEAR()/MONTH() on eu_release_date, which can't use an
            // index, so each one scanned all of games. Now they're date ranges, this index lets
            // MySQL go straight to the console and month. console_id first because every
            // calendar query has it as an equality. See improvements #181.
            $table->index(['console_id', 'eu_release_date']);
        });
    }

    public function down()
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropIndex(['console_id', 'eu_release_date']);
        });
    }
}
