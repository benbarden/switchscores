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
        Schema::table('consoles', function(Blueprint $table) {
            $table->string('slug', 30);
        });
        DB::update("UPDATE consoles SET slug = 'switch-1' WHERE id = 1");
        DB::update("UPDATE consoles SET slug = 'switch-2' WHERE id = 2");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consoles', function(Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
