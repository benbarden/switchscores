<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE games ADD COLUMN nintendo_description TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL AFTER game_description');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE games DROP COLUMN nintendo_description');
    }
};
