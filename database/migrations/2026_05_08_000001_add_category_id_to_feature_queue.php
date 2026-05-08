<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('feature_queue', function (Blueprint $table) {
            $table->unsignedInteger('category_id')->nullable()->after('game_id');

            $table->dropUnique('uniq_bucket_game');
            $table->unique(['bucket', 'game_id', 'category_id'], 'uniq_bucket_game_category');

            $table->foreign('category_id')->references('id')->on('categories')->nullOnDelete();
            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::table('feature_queue', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropIndex(['category_id']);
            $table->dropUnique('uniq_bucket_game_category');
            $table->unique(['bucket', 'game_id'], 'uniq_bucket_game');
            $table->dropColumn('category_id');
        });
    }
};
