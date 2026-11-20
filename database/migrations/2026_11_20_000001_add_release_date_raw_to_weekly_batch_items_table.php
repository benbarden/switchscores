<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReleaseDateRawToWeeklyBatchItemsTable extends Migration
{
    public function up()
    {
        Schema::table('weekly_batch_items', function (Blueprint $table) {
            // The date as the store shows it. Kept for games with no exact date ("May 2027",
            // "2027"), which can't be imported yet, so the Raw page can show what Nintendo says.
            $table->string('release_date_raw', 50)->nullable()->after('release_date');
        });
    }

    public function down()
    {
        Schema::table('weekly_batch_items', function (Blueprint $table) {
            $table->dropColumn('release_date_raw');
        });
    }
}
