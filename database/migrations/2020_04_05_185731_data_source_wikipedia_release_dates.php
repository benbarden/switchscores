<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DataSourceWikipediaReleaseDates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('data_source_parsed', function(Blueprint $table) {
            $table->date('release_date_us')->nullable()->after('release_date_eu');
            $table->date('release_date_jp')->nullable()->after('release_date_us');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('data_source_parsed', function(Blueprint $table) {
            $table->dropColumn('release_date_jp');
            $table->dropColumn('release_date_us');
        });
    }
}
