<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTitleToDataSourceImportLog extends Migration
{
    public function up()
    {
        Schema::table('data_source_import_log', function (Blueprint $table) {
            $table->string('title')->nullable()->after('link_id');
        });
    }

    public function down()
    {
        Schema::table('data_source_import_log', function (Blueprint $table) {
            $table->dropColumn('title');
        });
    }
}
