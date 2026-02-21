<?php

use App\Enums\GameStatus;
use App\Models\Game;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('games', function(Blueprint $table) {
            $table->string('game_status', 20)->default(GameStatus::ACTIVE->value)->after('format_digital')->index();
        });

        // Populate: games that are de-listed should have game_status = 'delisted'
        DB::table('games')
            ->where('format_digital', Game::FORMAT_DELISTED)
            ->update(['game_status' => GameStatus::DELISTED->value]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('games', function(Blueprint $table) {
            $table->dropColumn('game_status');
        });
    }
};
