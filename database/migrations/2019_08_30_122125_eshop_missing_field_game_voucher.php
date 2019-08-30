<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EshopMissingFieldGameVoucher extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('eshop_europe_games', function(Blueprint $table) {
            $table->tinyInteger('switch_game_voucher_b')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('eshop_europe_games', function(Blueprint $table) {
            $table->dropColumn('switch_game_voucher_b');
        });
    }
}
