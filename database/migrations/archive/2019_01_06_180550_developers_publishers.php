<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DevelopersPublishers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('developers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100);
            $table->string('link_title', 100);
            $table->text('website_url')->nullable();
            $table->string('twitter_id', 20)->nullable();

            $table->timestamps();

            $table->index('link_title', 'link_title');
        });

        Schema::create('publishers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100);
            $table->string('link_title', 100);
            $table->text('website_url')->nullable();
            $table->string('twitter_id', 20)->nullable();

            $table->timestamps();

            $table->index('link_title', 'link_title');
        });

        Schema::create('game_developers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('game_id');
            $table->integer('developer_id');

            $table->index('game_id', 'game_id');
            $table->index('developer_id', 'developer_id');

            $table->timestamps();
        });

        Schema::create('game_publishers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('game_id');
            $table->integer('publisher_id');

            $table->index('game_id', 'game_id');
            $table->index('publisher_id', 'publisher_id');

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
        Schema::dropIfExists('game_developers');
        Schema::dropIfExists('game_publishers');
        Schema::dropIfExists('developers');
        Schema::dropIfExists('publishers');
    }
}
