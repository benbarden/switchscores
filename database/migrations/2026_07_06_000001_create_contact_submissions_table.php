<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContactSubmissionsTable extends Migration
{
    public function up()
    {
        Schema::create('contact_submissions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100);
            $table->string('email', 150);
            $table->string('request_type', 50);
            $table->text('message');
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('status', 20)->default('new');
            $table->timestamps();

            $table->index('status');
            $table->index('email');
        });
    }

    public function down()
    {
        Schema::dropIfExists('contact_submissions');
    }
}
