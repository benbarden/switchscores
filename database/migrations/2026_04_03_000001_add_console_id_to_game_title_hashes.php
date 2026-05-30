<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add console_id column
        Schema::table('game_title_hashes', function (Blueprint $table) {
            $table->integer('console_id')->after('game_id')->nullable();
            $table->index('console_id', 'console_id');
        });

        // Populate console_id from games table
        DB::statement("
            UPDATE game_title_hashes gth
            JOIN games g ON gth.game_id = g.id
            SET gth.console_id = g.console_id
        ");

        // Make console_id non-nullable now that it's populated
        Schema::table('game_title_hashes', function (Blueprint $table) {
            $table->integer('console_id')->nullable(false)->change();
        });

        // Add composite unique index for per-console uniqueness
        Schema::table('game_title_hashes', function (Blueprint $table) {
            $table->unique(['title_hash', 'console_id'], 'title_hash_console_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('game_title_hashes', function (Blueprint $table) {
            $table->dropUnique('title_hash_console_unique');
            $table->dropIndex('console_id');
            $table->dropColumn('console_id');
        });
    }
};
