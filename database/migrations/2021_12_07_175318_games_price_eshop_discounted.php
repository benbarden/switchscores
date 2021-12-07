<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class GamesPriceEshopDiscounted extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('games', function(Blueprint $table) {
            $table->decimal('price_eshop_discounted', 6, 2)->nullable();
            $table->decimal('price_eshop_discount_pc', 4, 1)->nullable();
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
            $table->dropColumn('price_eshop_discounted');
            $table->dropColumn('price_eshop_discount_pc');
        });
    }
}
