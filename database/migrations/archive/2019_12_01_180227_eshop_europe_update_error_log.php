<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EshopEuropeUpdateErrorLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eshop_europe_alerts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('game_id');
            $table->integer('type');
            $table->text('error_message');
            $table->text('current_data')->nullable();
            $table->text('new_data')->nullable();

            $table->timestamps();

            $table->index('game_id', 'game_id');
            $table->index('type', 'type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('eshop_europe_alerts');
    }
}
