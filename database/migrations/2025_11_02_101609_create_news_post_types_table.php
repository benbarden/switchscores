<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('news_post_types', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 40)->unique();   // e.g. almost_ranked
            $table->string('title', 120);
            $table->unsignedSmallInteger('cadence_days')->default(14);
            $table->timestamp('last_published_at')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });

        DB::table('news_post_types')->upsert([
            ['slug' => 'needs-2-reviews', 'title' => 'Games that need one more review', 'cadence_days' => 14, 'is_enabled' => true],
            ['slug' => 'needs-1-review', 'title' => 'Promising games (only 1 review so far)', 'cadence_days' => 14, 'is_enabled' => true],
            ['slug' => 'needs-0-reviews', 'title' => 'New/overlooked games (no reviews yet)', 'cadence_days' => 14, 'is_enabled' => true],
            ['slug' => 'newly-ranked', 'title' => 'Newly ranked games', 'cadence_days' => 30, 'is_enabled' => true],
            ['slug' => 'forgotten-gem', 'title' => 'Forgotten gems worth a look', 'cadence_days' => 21, 'is_enabled' => true],
        ], ['slug'], ['title', 'cadence_days', 'is_enabled']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news_post_types');
    }
};
