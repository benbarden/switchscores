<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLinkTitleIndexToGamesTable extends Migration
{
    public function up()
    {
        Schema::table('games', function (Blueprint $table) {
            // The game page looks up the other-console version of a game by link_title
            // (WHERE link_title = ? AND console_id != ?). With no index here, MySQL fell back to
            // console_id, and `!=` matches about half the table: ~9k rows scanned per lookup,
            // 300-500ms in the slow-query log. Single column on purpose: link_title equality
            // narrows to one or two rows (a game and its other-console version), so adding
            // console_id would gain nothing. Not unique - S1 and S2 versions share a link_title.
            // See improvements #179.
            $table->index('link_title');
        });
    }

    public function down()
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropIndex(['link_title']);
        });
    }
}
