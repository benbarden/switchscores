<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRunIdToDataSourceImportLog extends Migration
{
    public function up()
    {
        Schema::table('data_source_import_log', function (Blueprint $table) {
            $table->unsignedBigInteger('run_id')->nullable()->after('id');
            $table->index('run_id');
        });
    }

    public function down()
    {
        Schema::table('data_source_import_log', function (Blueprint $table) {
            $table->dropIndex(['run_id']);
            $table->dropColumn('run_id');
        });
    }
}
