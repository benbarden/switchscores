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
        Schema::table('games', function(Blueprint $table) {
            $table->text('game_description')->nullable();
            $table->tinyInteger('one_to_watch')->default(0);

            $table->index('one_to_watch', 'one_to_watch');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('games', function(Blueprint $table) {
            $table->dropColumn('game_description');
            $table->dropColumn('one_to_watch');
        });
    }
};
