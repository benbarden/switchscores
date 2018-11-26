<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GameTags extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->increments('id');
            $table->string('tag_name', 50);

            $table->timestamps();
        });

        Schema::create('game_tags', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('game_id');
            $table->integer('tag_id');

            $table->index('game_id', 'game_id');
            $table->index('tag_id', 'tag_id');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('game_tags');
        Schema::dropIfExists('tags');
    }
}
