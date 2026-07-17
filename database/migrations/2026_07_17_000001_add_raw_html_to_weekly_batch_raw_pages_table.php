<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRawHtmlToWeeklyBatchRawPagesTable extends Migration
{
    public function up()
    {
        Schema::table('weekly_batch_raw_pages', function (Blueprint $table) {
            // Rich HTML captured from the Nintendo store on paste, alongside the
            // plain-text raw_content. When present, the parser reads this instead.
            $table->longText('raw_html')->nullable()->after('raw_content');
        });
    }

    public function down()
    {
        Schema::table('weekly_batch_raw_pages', function (Blueprint $table) {
            $table->dropColumn('raw_html');
        });
    }
}
