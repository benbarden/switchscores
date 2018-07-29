<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GamesBoxartExternalLinks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('games', function(Blueprint $table) {
            $table->text('boxart_url')->nullable();
            $table->text('boxart_square_url')->nullable();
            $table->text('vendor_page_url')->nullable();
            $table->text('nintendo_page_url')->nullable();
            $table->string('twitter_id', 30)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('games', function(Blueprint $table) {
            $table->dropColumn('boxart_url');
            $table->dropColumn('boxart_square_url');
            $table->dropColumn('vendor_page_url');
            $table->dropColumn('nintendo_page_url');
            $table->dropColumn('twitter_id');
        });
    }
}
