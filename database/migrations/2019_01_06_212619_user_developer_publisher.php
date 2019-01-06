<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UserDeveloperPublisher extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function(Blueprint $table) {
            $table->integer('developer_id')->nullable();
            $table->integer('publisher_id')->nullable();
            $table->index('developer_id', 'developer_id');
            $table->index('publisher_id', 'publisher_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function(Blueprint $table) {
            $table->dropColumn('developer_id');
            $table->dropColumn('publisher_id');
        });
    }
}
