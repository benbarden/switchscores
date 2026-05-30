<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE games MODIFY game_description TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE games MODIFY game_description TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL');
    }
};
