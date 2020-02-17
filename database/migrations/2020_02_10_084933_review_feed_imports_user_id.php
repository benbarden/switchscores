<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ReviewFeedImportsUserId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('review_feed_imports', function(Blueprint $table) {
            $table->integer('user_id')->nullable();

            $table->index('user_id', 'user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('review_feed_imports', function(Blueprint $table) {
            $table->dropColumn('user_id');
        });
    }
}
