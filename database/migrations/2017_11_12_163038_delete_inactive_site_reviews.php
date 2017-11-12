<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DeleteInactiveSiteReviews extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Total: 1221
        // Total to be removed: 77
        // Total after removal: 1144
        //  7: 34 reviews
        // 10: 24 reviews
        // 16: 19 reviews
        DB::delete("DELETE FROM review_links WHERE site_id IN (7, 10, 16)");
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
