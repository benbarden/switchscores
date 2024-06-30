<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ReviewDrafts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('review_drafts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('source', 20);
            $table->integer('site_id')->nullable();
            $table->integer('user_id')->nullable();
            $table->integer('game_id')->nullable();
            $table->string('item_url', 200);
            $table->string('item_title', 200);
            $table->string('parsed_title', 200)->nullable();
            $table->dateTime('item_date')->nullable();
            $table->decimal('item_rating', 4, 1)->nullable();
            $table->text('parse_status')->nullable();
            $table->text('process_status')->nullable();
            $table->integer('review_link_id')->nullable();
            $table->timestamps();

            $table->index('site_id', 'site_id');
            $table->index('game_id', 'game_id');
            $table->unique('item_url', 'item_url');
            $table->index('review_link_id', 'review_link_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('review_drafts');
    }
}
