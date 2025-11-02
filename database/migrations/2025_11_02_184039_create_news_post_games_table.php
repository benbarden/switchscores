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
        Schema::create('news_post_games', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('news_post_id');
            $table->unsignedBigInteger('game_id');
            $table->string('bucket', 40);
            $table->unsignedBigInteger('feature_queue_id')->nullable();
            $table->timestamps();
            $table->unique(['news_post_id','game_id']);
            $table->index('news_post_id');
            $table->index('game_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news_post_games');
    }
};
