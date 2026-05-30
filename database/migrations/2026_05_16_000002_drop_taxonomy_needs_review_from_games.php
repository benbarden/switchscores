<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn('taxonomy_needs_review');
        });
    }

    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->unsignedInteger('taxonomy_needs_review')->default(0)->after('is_low_quality');
        });
    }
};
