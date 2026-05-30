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
        Schema::table('game_scraped_data', function (Blueprint $table) {
            $table->string('header_image_url', 500)->nullable()->after('features_json');
            $table->unsignedInteger('header_image_size')->nullable()->after('header_image_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('game_scraped_data', function (Blueprint $table) {
            $table->dropColumn('header_image_url');
            $table->dropColumn('header_image_size');
        });
    }
};
