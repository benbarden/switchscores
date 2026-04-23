<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateQuickReviewsInactiveToRejected extends Migration
{
    public function up()
    {
        DB::table('quick_reviews')->where('item_status', 9)->update(['item_status' => 2]);
    }

    public function down()
    {
        DB::table('quick_reviews')->where('item_status', 2)->update(['item_status' => 9]);
    }
}
