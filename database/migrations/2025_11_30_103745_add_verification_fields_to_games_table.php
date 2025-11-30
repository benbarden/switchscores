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
            // Add two fields for verification
            $table->tinyInteger('category_verification')
                ->default(0)   // 0 = unverified
                ->comment('0 = unverified, 1 = verified, 2 = needs review')
                ->after('category_id');

            $table->tinyInteger('tags_verification')
                ->default(0)   // 0 = unverified
                ->comment('0 = unverified, 1 = verified, 2 = needs review')
                ->after('category_verification');
        });

        // OPTIONAL: migrate existing taxonomy_needs_review status
        // If taxonomy_needs_review = 1, mark both new fields as 2 (needs review)
        if (Schema::hasColumn('games', 'taxonomy_needs_review')) {
            DB::table('games')
                ->where('taxonomy_needs_review', 1)
                ->update([
                    'category_verification' => 2,
                    'tags_verification' => 2,
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn('category_verification');
            $table->dropColumn('tags_verification');
        });
    }
};
