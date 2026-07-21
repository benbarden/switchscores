<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Append-only log of per-SKU price CHANGES.
 *
 * Written only when a price actually differs from what is already stored, not on
 * every run. A run that changes nothing writes nothing.
 *
 * Why append-on-change rather than a row per NSUID per run: at Switch 2 scale that
 * would be ~583 rows a day, and at Switch 1 scale ~9,500 - hundreds of thousands of
 * rows a year, almost all of them identical to the row before. Changes are rare, so
 * this stays small and every row in it is interesting by construction.
 *
 * Why it exists at all: a price that has already been overwritten cannot be
 * reconstructed. The 2026-07-20 session repeatedly needed "what was this before, and
 * when did it change?" and had to re-derive it from the eShop by hand - notably for
 * A-Train, whose ignore_price freeze had no recorded reason.
 */
class CreateDataSourcePriceHistory extends Migration
{
    public function up()
    {
        Schema::create('data_source_price_history', function (Blueprint $table) {
            $table->id();

            $table->string('nsuid', 20);

            // Both sides of the change, so a row is readable on its own without
            // needing to find the previous row.
            $table->decimal('old_regular_price', 8, 2)->nullable();
            $table->decimal('new_regular_price', 8, 2)->nullable();
            $table->decimal('old_discount_price', 8, 2)->nullable();
            $table->decimal('new_discount_price', 8, 2)->nullable();

            $table->timestamp('recorded_at')->useCurrent();

            $table->index(['nsuid', 'recorded_at']);
            $table->index('recorded_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('data_source_price_history');
    }
}
