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
        // Add last_crawl_status to games table
        Schema::table('games', function(Blueprint $table) {
            $table->smallInteger('last_crawl_status')->nullable()->after('last_crawled_at');
        });

        // Create lifecycle table for tracking problems and recoveries
        Schema::create('game_crawl_lifecycle', function(Blueprint $table) {
            $table->id();
            $table->unsignedInteger('game_id');
            $table->smallInteger('status_code');
            $table->string('url_crawled', 500)->nullable();
            $table->timestamp('crawled_at');
            $table->timestamps();

            $table->foreign('game_id')->references('id')->on('games');
            $table->index(['game_id', 'crawled_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_crawl_lifecycle');

        Schema::table('games', function(Blueprint $table) {
            $table->dropColumn('last_crawl_status');
        });
    }
};
