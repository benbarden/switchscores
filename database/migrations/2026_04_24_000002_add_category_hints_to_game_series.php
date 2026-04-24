<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_series', function (Blueprint $table) {
            $table->json('category_hints')->nullable()->after('link_title');
        });
    }

    public function down(): void
    {
        Schema::table('game_series', function (Blueprint $table) {
            $table->dropColumn('category_hints');
        });
    }
};
