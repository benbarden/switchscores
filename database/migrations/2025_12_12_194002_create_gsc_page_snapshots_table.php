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
        Schema::create('gsc_page_snapshots', function (Blueprint $table) {
            $table->id();

            // Identification
            $table->string('page_url', 1024);
            $table->string('page_type', 32); // game | category | top_rated
            $table->unsignedInteger('game_id')->nullable()->index();

            // Snapshot metadata
            $table->date('snapshot_date');
            $table->unsignedSmallInteger('window_days'); // e.g. 28

            // Metrics (aggregated per page)
            $table->unsignedInteger('clicks')->default(0);
            $table->unsignedInteger('impressions')->default(0);
            $table->decimal('avg_position', 6, 2)->nullable();

            // Context
            $table->unsignedSmallInteger('query_count')->default(0);
            $table->json('top_queries')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['snapshot_date', 'page_type']);
            $table->index(['page_type']);
            $table->index(['page_url']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gsc_page_snapshots');
    }
};
