<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class GameFormatOptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('games', function(Blueprint $table) {
            $table->string('format_digital')->nullable();
            $table->string('format_physical')->nullable();
            $table->string('format_dlc')->nullable();
            $table->string('format_demo')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('games', function(Blueprint $table) {
            $table->dropColumn('format_digital');
            $table->dropColumn('format_physical');
            $table->dropColumn('format_dlc');
            $table->dropColumn('format_demo');
        });
    }
}
