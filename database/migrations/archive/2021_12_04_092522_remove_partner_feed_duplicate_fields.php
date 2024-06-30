<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemovePartnerFeedDuplicateFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('partners', function(Blueprint $table) {
            $table->dropColumn('feed_url');
            $table->dropColumn('feed_url_prefix');
            $table->dropColumn('title_match_rule_pattern');
            $table->dropColumn('title_match_index');
            $table->dropColumn('allow_historic_content');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('partners', function(Blueprint $table) {
            $table->string('feed_url', 255)->nullable();
            $table->string('feed_url_prefix', 50)->nullable();
            $table->text('title_match_rule_pattern')->nullable();
            $table->integer('title_match_index')->nullable();
            $table->tinyInteger('allow_historic_content')->nullable();
        });
    }
}
