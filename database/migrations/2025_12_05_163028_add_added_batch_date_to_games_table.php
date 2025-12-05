<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->date('added_batch_date')->nullable()->after('created_at');
            $table->index('added_batch_date', 'added_batch_date');
        });

        DB::statement("
            UPDATE games
            SET added_batch_date = DATE(created_at)
            WHERE added_batch_date IS NULL;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn('added_batch_date');
        });
    }
};
