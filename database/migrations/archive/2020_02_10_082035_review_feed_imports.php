<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ReviewFeedImports extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::statement('RENAME TABLE feed_item_reviews TO review_feed_items');

        Schema::create('review_feed_imports', function (Blueprint $table) {
            $table->increments('id');
            $table->string('import_method', 20);
            $table->integer('site_id')->nullable();
            $table->timestamps();

            $table->index('site_id', 'site_id');
        });

        Schema::table('review_feed_items', function(Blueprint $table) {
            $table->integer('import_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('review_feed_items', function(Blueprint $table) {
            $table->dropColumn('import_id');
        });
        \DB::statement('RENAME TABLE review_feed_items TO feed_item_reviews');
        Schema::dropIfExists('review_feed_imports');
    }
}
