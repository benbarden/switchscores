<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class LowQualityFilter extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('games', function(Blueprint $table) {
            $table->tinyInteger('is_low_quality')->default(0);
            $table->index('is_low_quality', 'is_low_quality');
        });
        Schema::table('partners', function(Blueprint $table) {
            $table->tinyInteger('is_low_quality')->default(0);
            $table->index('is_low_quality', 'is_low_quality');
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
            $table->dropColumn('is_low_quality');
        });
        Schema::table('partners', function(Blueprint $table) {
            $table->dropColumn('is_low_quality');
        });
    }
}
