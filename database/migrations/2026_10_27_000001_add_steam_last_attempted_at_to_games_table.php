<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The Steam reviews sync queue runs on the last attempt, not the last success, so a game
 * that keeps failing drops behind the others instead of coming up first every night. It lives on
 * games rather than steam_review_data because a game that has never synced has no row there.
 * Backfilled from last_synced_at so the queue keeps its current order.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->timestamp('steam_last_attempted_at')->nullable()->after('steam_status');
        });

        DB::statement('
            UPDATE games g
            JOIN steam_review_data srd ON srd.game_id = g.id
            SET g.steam_last_attempted_at = srd.last_synced_at
        ');
    }

    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn('steam_last_attempted_at');
        });
    }
};
