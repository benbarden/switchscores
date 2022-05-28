<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DeleteFeedImports extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('review_feed_imports');
        Schema::table('review_feed_items', function(Blueprint $table) {
            $table->dropColumn('import_id');
        });
        Schema::table('review_feed_items_test', function(Blueprint $table) {
            $table->dropColumn('import_id');
        });
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
