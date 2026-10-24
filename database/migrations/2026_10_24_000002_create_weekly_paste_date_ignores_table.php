<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * #194: release dates from the weekly paste that staff chose not to apply. Keyed on the game and
 * the pasted date, so a later paste with a different date shows as a difference again.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weekly_paste_date_ignores', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('game_id');
            $table->date('release_date');
            $table->timestamps();

            $table->unique(['game_id', 'release_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weekly_paste_date_ignores');
    }
};
