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
        Schema::table('games', function (Blueprint $table) {
            $table->string('amazon_us_status', 20)
                ->default('unchecked')
                ->after('amazon_us_link');

            $table->string('amazon_uk_status', 20)
                ->default('unchecked')
                ->after('amazon_uk_link');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn([
                'amazon_us_status',
                'amazon_uk_status',
            ]);
        });
    }
};
