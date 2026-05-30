<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->string('steam_id')->nullable()->after('amazon_us_asin');
            $table->enum('steam_status', ['not_checked', 'linked', 'not_on_steam'])->default('not_checked')->after('steam_id');
        });
    }

    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn(['steam_id', 'steam_status']);
        });
    }
};
