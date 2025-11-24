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
        Schema::table('categories', function (Blueprint $table) {
            $table->string('layout_version', 30)->default('layout-v1');
            $table->string('meta_description', 255)->nullable();
            $table->text('intro_description')->nullable();
        });

        Schema::table('tags', function (Blueprint $table) {
            $table->string('layout_version', 30)->default('layout-v1');
            $table->string('meta_description', 255)->nullable();
            $table->text('intro_description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['layout_version', 'meta_description', 'intro_description']);
        });

        Schema::table('tags', function (Blueprint $table) {
            $table->dropColumn(['layout_version', 'meta_description', 'intro_description']);
        });
    }
};
