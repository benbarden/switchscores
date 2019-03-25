<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EshopUsGame extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eshop_us_games', function (Blueprint $table) {

            $table->increments('id');

            $table->integer('nsuid');
            $table->index('nsuid', 'nsuid');

            $table->text('title')->nullable();
            $table->text('categories')->nullable();             // Array - convert to JSON
            $table->text('slug')->nullable();
            $table->tinyInteger('buyitnow')->nullable();        // Boolean
            $table->date('release_date')->nullable();
            $table->tinyInteger('digitaldownload')->nullable(); // Boolean
            $table->tinyInteger('nso')->nullable();             // Boolean
            $table->tinyInteger('free_to_start')->nullable();   // Boolean
            $table->text('system')->nullable();
            $table->text('ncom_id')->nullable();
            $table->decimal('ca_price', 6, 2)->nullable();
            $table->text('number_of_players')->nullable();
            $table->text('video_link')->nullable();
            $table->decimal('eshop_price', 6, 2)->nullable();
            $table->text('front_box_art')->nullable();
            $table->text('game_code')->nullable();
            $table->tinyInteger('buyonline')->nullable();       // Boolean
            $table->decimal('sale_price', 6, 2)->nullable();
            $table->text('release_date_display')->nullable();

            $table->timestamps();
        });

        Schema::table('games', function(Blueprint $table) {
            $table->integer('eshop_us_nsuid')->nullable();
            $table->index('eshop_us_nsuid', 'eshop_us_nsuid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('eshop_us_games');

        Schema::table('games', function(Blueprint $table) {
            $table->dropColumn('eshop_us_nsuid');
        });
    }
}
