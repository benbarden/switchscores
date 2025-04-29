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
        Schema::table('game_rank_alltime', function(Blueprint $table) {
            $table->integer('console_id')->default(1)->after('id');
            $table->index('console_id', 'console_id');
        });
        Schema::table('game_rank_year', function(Blueprint $table) {
            $table->integer('console_id')->default(1)->after('id');
            $table->index('console_id', 'console_id');
        });
        Schema::table('game_rank_yearmonth', function(Blueprint $table) {
            $table->integer('console_id')->default(1)->after('id');
            $table->index('console_id', 'console_id');
        });
        Schema::table('game_calendar_stats', function(Blueprint $table) {
            $table->integer('console_id')->default(1)->after('month_name');
            $table->index('console_id', 'console_id');
        });

        DB::update("UPDATE game_rank_alltime SET console_id = 1");
        DB::update("UPDATE game_rank_year SET console_id = 1");
        DB::update("UPDATE game_rank_yearmonth SET console_id = 1");
        DB::update("UPDATE game_calendar_stats SET console_id = 1");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('game_rank_alltime', function(Blueprint $table) {
            $table->dropColumn('console_id');
        });
        Schema::table('game_rank_year', function(Blueprint $table) {
            $table->dropColumn('console_id');
        });
        Schema::table('game_rank_yearmonth', function(Blueprint $table) {
            $table->dropColumn('console_id');
        });
        Schema::table('game_calendar_stats', function(Blueprint $table) {
            $table->dropColumn('console_id');
        });
    }
};
