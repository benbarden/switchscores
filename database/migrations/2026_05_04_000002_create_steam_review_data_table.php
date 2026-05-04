<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('steam_review_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('game_id')->unique();
            $table->string('steam_id');
            $table->tinyInteger('review_score')->nullable();
            $table->string('review_score_desc')->nullable();
            $table->unsignedInteger('total_positive')->default(0);
            $table->unsignedInteger('total_negative')->default(0);
            $table->unsignedInteger('total_reviews')->default(0);
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('steam_review_data');
    }
};
