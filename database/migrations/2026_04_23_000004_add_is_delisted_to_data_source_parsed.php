<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsDelistedToDataSourceParsed extends Migration
{
    public function up()
    {
        Schema::table('data_source_parsed', function (Blueprint $table) {
            $table->boolean('is_delisted')->default(0)->after('game_id');
        });
    }

    public function down()
    {
        Schema::table('data_source_parsed', function (Blueprint $table) {
            $table->dropColumn('is_delisted');
        });
    }
}
