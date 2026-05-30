<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('weekly_batch_items', function (Blueprint $table) {
            $table->tinyInteger('suggestion_accepted')->nullable()->after('suggested_category');
        });
    }

    public function down(): void
    {
        Schema::table('weekly_batch_items', function (Blueprint $table) {
            $table->dropColumn('suggestion_accepted');
        });
    }
};
