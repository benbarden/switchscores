<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChartsRankingsGlobal extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('charts_rankings_global', function (Blueprint $table) {
            $table->increments('id');
            $table->date('chart_date');
            $table->addColumn('tinyInteger', 'position', ['length' => 2, 'unsigned' => true]);
            $table->integer('game_id');
            $table->string('country_code', 3);
            $table->timestamps();

            $table->index('chart_date', 'chart_date');
            $table->index('game_id', 'game_id');
            $table->index(['chart_date', 'country_code'], 'chart_date_country_code');
        });

        DB::insert("
            INSERT INTO charts_rankings_global(chart_date, position, game_id, country_code, created_at, updated_at)
            SELECT chart_date, position, game_id, 'EU', NOW(), NOW()
            FROM charts_rankings
        ");
        DB::insert("
            INSERT INTO charts_rankings_global(chart_date, position, game_id, country_code, created_at, updated_at)
            SELECT chart_date, position, game_id, 'US', NOW(), NOW()
            FROM charts_rankings_us
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('charts_rankings_global');
    }
}
