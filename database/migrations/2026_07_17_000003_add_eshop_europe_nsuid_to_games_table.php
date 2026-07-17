<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEshopEuropeNsuidToGamesTable extends Migration
{
    public function up()
    {
        Schema::table('games', function (Blueprint $table) {
            // European eShop NSUID (the data-nsuid on Nintendo store listings).
            // Complements the existing eshop_us_nsuid. Nullable until backfilled;
            // lets weekly-batch items match to games by stable ID rather than title.
            $table->string('eshop_europe_nsuid', 20)->nullable()->after('eshop_europe_fs_id');
            $table->index('eshop_europe_nsuid', 'games_eshop_europe_nsuid_index');
        });
    }

    public function down()
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropIndex('games_eshop_europe_nsuid_index');
            $table->dropColumn('eshop_europe_nsuid');
        });
    }
}
