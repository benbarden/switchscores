<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * #62: "Not a Switch game review" replaces "Not a game review" and "Non-review content"
 * (112 and 30 drafts on localdev, 2026-10-27).
 *
 * down() can't tell the two old statuses apart, so it puts every row back as "Not a game review".
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('review_drafts')
            ->whereIn('process_status', ['Not a game review', 'Non-review content'])
            ->update(['process_status' => 'Not a Switch game review']);
    }

    public function down(): void
    {
        DB::table('review_drafts')
            ->where('process_status', 'Not a Switch game review')
            ->update(['process_status' => 'Not a game review']);
    }
};
