<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\User;
use App\Events\UserCreated;

class UserPointTransactions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_point_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('action_type_id');
            $table->integer('action_game_id')->nullable();
            $table->integer('points_credit')->nullable();
            $table->integer('points_debit')->nullable();
            $table->timestamps();

            $table->index('user_id', 'user_id');
            $table->index('action_type_id', 'action_type_id');
        });

        Schema::table('users', function(Blueprint $table) {
            $table->integer('points_balance');
        });

        $userList = User::all();
        foreach ($userList as $user) {
            event(new UserCreated($user));
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function(Blueprint $table) {
            $table->dropColumn('points_balance');
        });

        Schema::dropIfExists('user_point_transactions');
    }
}
