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
        Schema::table('tags', function (Blueprint $table) {
            $table->tinyInteger('taxonomy_reviewed')
                ->default(0)
                ->comment('0 = not reviewed, 1 = reviewed OK, 2 = deprecated')
                ->after('tag_category_id');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->tinyInteger('taxonomy_reviewed')
                ->default(0)
                ->comment('0 = not reviewed, 1 = reviewed OK, 2 = deprecated')
                ->after('parent_id');
        });

        Schema::table('tag_categories', function (Blueprint $table) {
            $table->tinyInteger('taxonomy_reviewed')
                ->default(0)
                ->comment('0 = not reviewed, 1 = reviewed OK, 2 = deprecated')
                ->after('category_order');
        });

        // Insert "Game Type" as category_order = 15 (between 10 and 20)
        $exists = DB::table('tag_categories')
            ->where('name', 'Game Type')
            ->exists();

        if (!$exists) {
            DB::table('tag_categories')->insert([
                'name' => 'Game type',
                'category_order' => 15,
                'taxonomy_reviewed' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tag_categories', function (Blueprint $table) {
            $table->dropColumn('taxonomy_reviewed');
        });

        DB::table('tag_categories')
            ->where('name', 'Game Type')
            ->delete();

        Schema::table('tags', function (Blueprint $table) {
            $table->dropColumn('taxonomy_reviewed');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('taxonomy_reviewed');
        });
    }
};
