<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_series', function (Blueprint $table) {
            $table->text('intro_description')->nullable()->after('link_title');
            $table->string('meta_description')->nullable()->after('intro_description');
        });
    }

    public function down(): void
    {
        Schema::table('game_series', function (Blueprint $table) {
            $table->dropColumn(['intro_description', 'meta_description']);
        });
    }
};
