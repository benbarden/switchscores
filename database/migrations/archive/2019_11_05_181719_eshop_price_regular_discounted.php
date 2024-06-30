<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EshopPriceRegularDiscounted extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('eshop_europe_games', function(Blueprint $table) {
            $table->decimal('price_regular_f', 6, 2)->nullable();
            $table->decimal('price_discounted_f', 6, 2)->nullable();
            $table->tinyInteger('eshop_removed_b')->nullable();
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
            $table->dropColumn('price_regular_f');
            $table->dropColumn('price_discounted_f');
            $table->dropColumn('eshop_removed_b');
        });
    }
}
