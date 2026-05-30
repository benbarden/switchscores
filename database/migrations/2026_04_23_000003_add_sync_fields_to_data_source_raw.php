<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSyncFieldsToDataSourceRaw extends Migration
{
    public function up()
    {
        Schema::table('data_source_raw', function (Blueprint $table) {
            $table->string('content_hash', 32)->nullable()->after('link_id');
            $table->timestamp('last_seen_at')->nullable()->after('content_hash');
            $table->boolean('is_delisted')->default(0)->after('last_seen_at');
        });
    }

    public function down()
    {
        Schema::table('data_source_raw', function (Blueprint $table) {
            $table->dropColumn(['content_hash', 'last_seen_at', 'is_delisted']);
        });
    }
}
