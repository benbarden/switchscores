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
        Schema::table('games_companies', function(Blueprint $table) {
            $table->string('email', 255)->nullable();
            $table->string('threads_id', 50)->nullable();
            $table->string('bluesky_id', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('games_companies', function(Blueprint $table) {
            $table->dropColumn('email');
            $table->dropColumn('threads_id');
            $table->dropColumn('bluesky_id');
        });
    }
};
