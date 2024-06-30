<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DataSourceFormatOptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('data_source_parsed', function(Blueprint $table) {
            $table->tinyInteger('has_physical_version')->nullable();
            $table->tinyInteger('has_dlc')->nullable();
            $table->tinyInteger('has_demo')->nullable();
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
            $table->dropColumn('has_physical_version');
            $table->dropColumn('has_dlc');
            $table->dropColumn('has_demo');
        });
    }
}
