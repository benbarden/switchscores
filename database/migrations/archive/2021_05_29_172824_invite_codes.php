<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class InviteCodes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invite_codes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('invite_code', 100);
            $table->integer('times_used')->default(0);
            $table->integer('times_left')->default(0);
            $table->tinyInteger('is_active')->default(0);

            $table->timestamps();

            $table->unique('invite_code', 'invite_code');
        });

        Schema::table('users', function(Blueprint $table) {
            $table->integer('invite_code_id')->nullable();
            $table->index('invite_code_id', 'invite_code_id');
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
            $table->dropColumn('invite_code_id');
        });

        Schema::dropIfExists('invite_codes');
    }
}
