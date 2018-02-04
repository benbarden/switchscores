<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UserWishlist extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_lists', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('list_type');
            $table->string('list_name', 50);
            $table->tinyInteger('list_status');

            $table->timestamps();

            $table->index('user_id', 'user_id');
            $table->index('list_type', 'list_type');
        });

        Schema::create('user_list_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('list_id');
            $table->integer('game_id');

            $table->timestamps();

            $table->index('list_id', 'list_id');
            $table->index('game_id', 'game_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_lists');
        Schema::dropIfExists('user_list_items');
    }
}
