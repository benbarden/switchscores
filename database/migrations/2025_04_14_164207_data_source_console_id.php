<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('data_source_raw', function(Blueprint $table) {
            $table->integer('console_id')->default(1)->after('source_id');
            $table->index('console_id', 'console_id');
        });
        Schema::table('data_source_parsed', function(Blueprint $table) {
            $table->integer('console_id')->default(1)->after('source_id');
            $table->index('console_id', 'console_id');
        });

        DB::update("UPDATE data_source_raw SET console_id = 1");
        DB::update("UPDATE data_source_parsed SET console_id = 1");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('data_source_raw', function(Blueprint $table) {
            $table->dropColumn('console_id');
        });
        Schema::table('data_source_parsed', function(Blueprint $table) {
            $table->dropColumn('console_id');
        });
    }
};
