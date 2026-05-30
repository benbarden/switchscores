<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check for duplicates before adding constraint
        $duplicates = \DB::select("
            SELECT link_title, COUNT(*) as count
            FROM tags
            GROUP BY link_title
            HAVING COUNT(*) > 1
        ");

        if (count($duplicates) > 0) {
            throw new \Exception(
                'Cannot add unique constraint: duplicate link_title values exist. ' .
                'Please resolve duplicates first: ' .
                implode(', ', array_map(fn($d) => $d->link_title, $duplicates))
            );
        }

        Schema::table('tags', function (Blueprint $table) {
            $table->unique('link_title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tags', function (Blueprint $table) {
            $table->dropUnique(['link_title']);
        });
    }
};
