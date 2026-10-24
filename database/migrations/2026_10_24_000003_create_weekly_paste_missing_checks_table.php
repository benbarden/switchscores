<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * #195: games missing from the latest weekly paste that staff have checked. One row per game;
 * checked_on is the date of the last check, so a game that appears in a later paste and then
 * goes missing again shows again.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weekly_paste_missing_checks', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('game_id')->unique();
            $table->date('checked_on');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weekly_paste_missing_checks');
    }
};
