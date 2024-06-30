<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ReviewDate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('review_links', function(Blueprint $table) {
            $table->date('review_date')->nullable();
            $table->index('review_date', 'review_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('review_links', function(Blueprint $table) {
            $table->dropIndex('review_date');
            $table->dropColumn('review_date');
        });
    }
}
