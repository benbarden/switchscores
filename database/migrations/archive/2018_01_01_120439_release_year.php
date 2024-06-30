<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ReleaseYear extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('games', function(Blueprint $table) {
            $table->string('release_year')->nullable();
            $table->index('release_year', 'release_year');
        });

        DB::update("UPDATE games SET release_year = '2017' where release_date LIKE '2017-%'");
        DB::update("UPDATE games SET release_year = '2018' where release_date LIKE '2018-%'");
        DB::update("UPDATE games SET release_year = '2019' where release_date LIKE '2019-%'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('games', function(Blueprint $table) {
            $table->dropColumn('release_year');
        });
    }
}
