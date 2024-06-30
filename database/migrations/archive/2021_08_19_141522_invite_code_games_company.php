<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class InviteCodeGamesCompany extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invite_codes', function(Blueprint $table) {
            $table->integer('games_company_id')->nullable();
            $table->integer('reviewer_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invite_codes', function(Blueprint $table) {
            $table->dropColumn('games_company_id');
            $table->dropColumn('reviewer_id');
        });
    }
}
