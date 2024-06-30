<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ReviewSiteHistoricContentTitleMatching extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('review_sites', function(Blueprint $table) {
            $table->tinyInteger('allow_historic_content');
            $table->text('title_match_rule_pattern')->nullable();
            $table->integer('title_match_index')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('review_sites', function(Blueprint $table) {
            $table->dropColumn('title_match_index');
            $table->dropColumn('title_match_rule_pattern');
            $table->dropColumn('allow_historic_content');
        });
    }
}
