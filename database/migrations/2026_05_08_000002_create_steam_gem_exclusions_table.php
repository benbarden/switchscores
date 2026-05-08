<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('steam_gem_exclusions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('game_id');
            $table->string('reason', 100)->nullable();
            $table->timestamps();

            $table->unique('game_id');
            $table->foreign('game_id')->references('id')->on('games')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('steam_gem_exclusions');
    }
};
