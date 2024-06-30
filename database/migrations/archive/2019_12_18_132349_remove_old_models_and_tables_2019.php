<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveOldModelsAndTables2019 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::drop('charts_dates');
        Schema::drop('charts_rankings_global');
        Schema::drop('game_images');
        Schema::drop('mario_maker_levels');
        Schema::drop('user_lists');
        Schema::drop('user_list_items');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
