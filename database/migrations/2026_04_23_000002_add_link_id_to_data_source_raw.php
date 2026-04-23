<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLinkIdToDataSourceRaw extends Migration
{
    public function up()
    {
        Schema::table('data_source_raw', function (Blueprint $table) {
            $table->string('link_id', 50)->nullable()->after('console_id')->index();
        });
    }

    public function down()
    {
        Schema::table('data_source_raw', function (Blueprint $table) {
            $table->dropIndex(['link_id']);
            $table->dropColumn('link_id');
        });
    }
}
