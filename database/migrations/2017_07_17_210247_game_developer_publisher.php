<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GameDeveloperPublisher extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('games', function(Blueprint $table) {
            $table->string('developer', '100')->nullable();
            $table->string('publisher', '100')->nullable();
            $table->index('developer', 'developer');
            $table->index('publisher', 'publisher');
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
            $table->dropIndex('publisher');
            $table->dropIndex('developer');
            $table->dropColumn('publisher');
            $table->dropColumn('developer');
        });
    }
}
