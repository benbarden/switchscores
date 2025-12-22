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
            $table->string('amazon_uk_asin', 12)->nullable();
            $table->string('amazon_us_asin', 12)->nullable();

            $table->index('amazon_uk_asin');
            $table->index('amazon_us_asin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropIndex(['amazon_uk_asin']);
            $table->dropIndex(['amazon_us_asin']);

            $table->dropColumn([
                'amazon_uk_asin',
                'amazon_us_asin',
            ]);
        });
    }
};
