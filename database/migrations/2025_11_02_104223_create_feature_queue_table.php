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
        Schema::create('feature_queue', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('game_id');
            $table->string('bucket', 40);                  // e.g. 'almost_ranked'
            $table->decimal('priority', 5, 2)->default(0); // higher first
            $table->timestamp('queued_at')->useCurrent();
            $table->timestamp('used_at')->nullable();      // set when featured
            $table->string('notes', 255)->nullable();
            $table->timestamps();

            // indexes/uniques for speed + dedupe
            $table->unique(['bucket','game_id'], 'uniq_bucket_game');
            $table->index(['bucket','used_at'], 'idx_bucket_used');
            $table->index('game_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feature_queue');
    }
};
