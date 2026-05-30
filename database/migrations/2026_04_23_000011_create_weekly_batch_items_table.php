<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWeeklyBatchItemsTable extends Migration
{
    public function up()
    {
        Schema::create('weekly_batch_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('batch_id');
            $table->string('console', 10);      // switch-1, switch-2
            $table->string('list_type', 10);    // new, upcoming
            $table->unsignedTinyInteger('page_number');
            $table->unsignedSmallInteger('sort_order');  // position within page (raw paste order)

            // Core game data from raw parse
            $table->string('title');
            $table->string('title_raw');
            $table->date('release_date')->nullable();
            $table->decimal('price_gbp', 8, 2)->nullable();
            $table->string('price_raw', 50)->nullable();    // original string e.g. "£6.02£4.30*"
            $table->string('nintendo_genres', 200)->nullable(); // comma-separated from listing
            $table->text('description')->nullable();        // from listing page or game page fetch

            // User-provided
            $table->string('nintendo_url')->nullable();     // game page URL
            $table->string('packshot_url')->nullable();     // 1x1 square image URL

            // From Nintendo game page fetch
            $table->string('publisher_raw', 200)->nullable();
            $table->string('publisher_normalised', 200)->nullable();
            $table->string('players', 20)->nullable();      // e.g. "1", "1-4"

            // Category and collection
            $table->string('suggested_category', 100)->nullable();
            $table->string('category', 100)->nullable();
            $table->string('collection', 100)->nullable();  // link_title e.g. "arcade-archives-2"

            // Pipeline status
            $table->string('item_status', 30)->default('pending');
            // skipped:  already_in_db, out_of_range, low_quality, bundle
            // active:   pending, fetch_pending, lq_review, packshot_pending, category_pending, ready
            // done:     imported

            // LQ flagging
            $table->tinyInteger('lq_flag')->default(0);             // 1 = a flag triggered lq_review
            $table->string('lq_flag_reason', 200)->nullable();      // e.g. "publisher is_low_quality", "unknown publisher", "title pattern"
            $table->string('lq_publisher_name', 200)->nullable();   // publisher name at time of flag

            // Price flagging
            $table->tinyInteger('price_flag')->default(0);
            $table->string('price_flag_reason', 200)->nullable();   // e.g. "sale price detected", "£0.00"

            // Fetch tracking (granular — separate from item_status)
            $table->string('fetch_status', 20)->default('pending'); // pending, queued, fetching, fetched, failed
            $table->string('fetch_error', 500)->nullable();

            // Post-import
            $table->unsignedBigInteger('game_id')->nullable();      // set after import
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->foreign('batch_id')->references('id')->on('weekly_batches')->onDelete('cascade');
            $table->index(['batch_id', 'console', 'list_type'], 'wbi_batch_console_list_index');
            $table->index(['batch_id', 'item_status'], 'wbi_batch_status_index');
            $table->index(['batch_id', 'console', 'list_type', 'page_number', 'sort_order'], 'wbi_batch_order_index');
        });
    }

    public function down()
    {
        Schema::dropIfExists('weekly_batch_items');
    }
}
