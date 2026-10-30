<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPriceNoteToWeeklyBatchItemsTable extends Migration
{
    public function up()
    {
        Schema::table('weekly_batch_items', function (Blueprint $table) {
            // What the price API changed, shown on the Fetch and Prices steps.
            $table->string('price_note')->nullable()->after('price_flag_reason');
        });
    }

    public function down()
    {
        Schema::table('weekly_batch_items', function (Blueprint $table) {
            $table->dropColumn('price_note');
        });
    }
}
