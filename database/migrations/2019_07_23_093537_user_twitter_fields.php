<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UserTwitterFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function(Blueprint $table) {
            $table->string('twitter_user_id', 40)->nullable();
            $table->index('twitter_user_id', 'twitter_user_id');
            $table->string('twitter_name', 40)->nullable();
            $table->dateTime('login_date')->nullable();
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
            $table->dropColumn('login_date');
            $table->dropColumn('twitter_name');
            $table->dropColumn('twitter_user_id');
        });
    }
}
