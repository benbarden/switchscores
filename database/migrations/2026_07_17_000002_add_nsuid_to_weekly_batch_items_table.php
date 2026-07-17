<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNsuidToWeeklyBatchItemsTable extends Migration
{
    public function up()
    {
        Schema::table('weekly_batch_items', function (Blueprint $table) {
            // Nintendo eShop NSUID (stable per game+platform), captured from HTML paste.
            // Used to match items to existing games more reliably than by title.
            $table->string('nsuid', 20)->nullable()->after('title_raw');
            $table->index('nsuid', 'wbi_nsuid_index');
        });
    }

    public function down()
    {
        Schema::table('weekly_batch_items', function (Blueprint $table) {
            $table->dropIndex('wbi_nsuid_index');
            $table->dropColumn('nsuid');
        });
    }
}
