<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Current per-SKU prices from the Nintendo eShop price API.
 *
 * One row per NSUID, updated in place. Price CHANGES are appended to
 * data_source_price_history - see that migration for why the two are split.
 *
 * Keyed on the NSUID rather than the game, because the whole point of this
 * integration is that a game has several SKUs (standalone / upgrade pack / deluxe)
 * and the scalar price fields in the search payload cannot tell them apart.
 */
class CreateDataSourcePrices extends Migration
{
    public function up()
    {
        Schema::create('data_source_prices', function (Blueprint $table) {
            $table->id();

            // NSUIDs are 14-digit identifiers, not numbers. Stored as a string so they
            // are never subject to integer handling, and unique so an upsert is safe.
            $table->string('nsuid', 20)->unique();

            $table->unsignedTinyInteger('console_id')->nullable();

            // onsale / not_found / unreleased / sales_termination.
            // NOTE: "onsale" means PURCHASABLE, not DISCOUNTED. The discount signal is
            // discount_price being present.
            $table->string('sales_status', 30)->nullable();

            $table->decimal('regular_price', 8, 2)->nullable();
            $table->decimal('discount_price', 8, 2)->nullable();

            // end_datetime is the field that makes a stale discount structurally
            // impossible: a discount that has ended is knowable without waiting for the
            // API to stop sending it.
            $table->timestamp('discount_start_at')->nullable();
            $table->timestamp('discount_end_at')->nullable();

            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('fetched_at')->nullable();
            $table->timestamp('price_changed_at')->nullable();

            $table->index('console_id');
            $table->index('fetched_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('data_source_prices');
    }
}
