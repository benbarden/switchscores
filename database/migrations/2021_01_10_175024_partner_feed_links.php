<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PartnerFeedLinks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('partner_feed_links', function (Blueprint $table) {
            $table->increments('id');
            $table->tinyInteger('feed_status');
            $table->integer('site_id');
            $table->string('feed_url', 255);
            $table->string('feed_url_prefix', 50)->nullable();
            $table->tinyInteger('data_type');
            $table->tinyInteger('item_node');
            $table->text('title_match_rule_pattern');
            $table->integer('title_match_rule_index');
            $table->tinyInteger('allow_historic_content');
            $table->tinyInteger('was_last_run_successful')->nullable();
            $table->text('last_run_status')->nullable();
            $table->timestamps();
        });

        Schema::create('review_feed_items_test', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('site_id');
            $table->integer('game_id')->nullable();
            $table->string('item_url', 200);
            $table->string('item_title', 200);
            $table->string('parsed_title', 150)->nullable();
            $table->dateTime('item_date')->nullable();
            $table->decimal('item_rating', 4, 1)->nullable();
            $table->text('load_status')->nullable();
            $table->text('parse_status')->nullable();
            $table->tinyInteger('parsed')->nullable();
            $table->text('process_status')->nullable();
            $table->tinyInteger('processed')->nullable();
            $table->integer('import_id')->nullable();
            $table->timestamps();
        });

        Schema::table('review_feed_imports', function(Blueprint $table) {
            $table->tinyInteger('is_test')->default(0);
            $table->index('is_test', 'is_test');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('partner_feed_links');
        Schema::dropIfExists('review_feed_items_test');

        Schema::table('review_feed_imports', function(Blueprint $table) {
            $table->dropIndex('is_test');
            $table->dropColumn('is_test');
        });
    }
}
