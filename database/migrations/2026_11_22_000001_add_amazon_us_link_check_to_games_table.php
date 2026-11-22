<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAmazonUsLinkCheckToGamesTable extends Migration
{
    public function up()
    {
        Schema::table('games', function (Blueprint $table) {
            // The nightly Amazon US link check: when it last looked at the game's link and what it
            // found (AmazonLinkCheckResult). Our own result only, not Amazon's content.
            $table->dateTime('amazon_us_checked_at')->nullable()->after('amazon_us_status');
            $table->string('amazon_us_check_result', 20)->nullable()->after('amazon_us_checked_at');
            // The ASIN staff marked as fine when the check said to look by hand (a region edition,
            // or one the API can't see). A different ASIN shows on the list again.
            $table->string('amazon_us_check_ok_asin', 10)->nullable()->after('amazon_us_check_result');
        });
    }

    public function down()
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn(['amazon_us_checked_at', 'amazon_us_check_result', 'amazon_us_check_ok_asin']);
        });
    }
}
