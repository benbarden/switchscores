<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropPrimaryTypeIdLinkingFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('games', function(Blueprint $table) {
            $table->dropColumn('primary_type_id');
        });
        Schema::table('tags', function(Blueprint $table) {
            $table->dropColumn('primary_type_id');
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
            $table->integer('primary_type_id')->nullable();
        });

        Schema::table('tags', function(Blueprint $table) {
            $table->integer('primary_type_id')->nullable();
        });
    }
}
