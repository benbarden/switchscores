<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * #199: when a title is edited by hand, title_auto keeps what TitleNormaliser produced and title
 * holds the edit. Null means the title was never edited - the edited items are the ones to review
 * for TitleNormaliser changes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('weekly_batch_items', function (Blueprint $table) {
            $table->string('title_auto')->nullable()->after('title');
        });
    }

    public function down(): void
    {
        Schema::table('weekly_batch_items', function (Blueprint $table) {
            $table->dropColumn('title_auto');
        });
    }
};
