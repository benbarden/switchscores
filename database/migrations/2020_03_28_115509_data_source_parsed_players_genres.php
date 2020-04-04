<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use Illuminate\Support\Facades\DB;

class DataSourceParsedPlayersGenres extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('data_source_parsed', function(Blueprint $table) {
            $table->dropColumn('release_date');
            $table->date('release_date_eu')->nullable()->after('title');

            $table->dropColumn('price');
            $table->decimal('price_standard', 6, 2)->nullable()->after('release_date_eu');
            $table->decimal('price_discounted', 6, 2)->nullable()->after('price_standard');
            $table->decimal('price_discount_pc', 4, 1)->nullable()->after('price_discounted');

            $table->integer('link_id')->nullable()->after('game_id');
            $table->text('genres_json')->nullable()->after('publishers');
            $table->string('players', 10)->nullable()->after('genres_json');
            $table->text('url')->nullable()->after('players');

            $table->text('image_square')->nullable()->after('url');
            $table->text('image_header')->nullable()->after('image_square');

            $table->index('link_id', 'link_id');
        });

        Schema::table('games', function(Blueprint $table) {
            $table->text('image_square')->nullable()->after('series_id');
            $table->text('image_header')->nullable()->after('image_square');
        });

        DB::statement("
            INSERT INTO data_source_parsed(source_id, game_id, title, release_date_eu, price_standard)
            SELECT '1', id, title, eu_release_date, price_eshop FROM games;
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('data_source_parsed', function(Blueprint $table) {
            $table->dropColumn('link_id');
            $table->dropColumn('genres_json');
            $table->dropColumn('players');
            $table->dropColumn('url');
            $table->dropColumn('image_square');
            $table->dropColumn('image_header');

            $table->dropColumn('price_standard');
            $table->dropColumn('price_discounted');
            $table->dropColumn('price_discount_pc');
            $table->string('price', 20)->nullable()->after('title');

            $table->dropColumn('release_date_eu');
            $table->date('release_date')->nullable()->after('price');
        });

        Schema::table('games', function(Blueprint $table) {
            $table->dropColumn('image_square');
            $table->dropColumn('image_header');
        });

        DB::statement('DELETE FROM data_source_parsed WHERE source_id = 1');
    }
}
