<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContactBlocklistTable extends Migration
{
    public function up()
    {
        Schema::create('contact_blocklist', function (Blueprint $table) {
            $table->increments('id');
            $table->string('value', 150);
            $table->string('type', 10);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['value', 'type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('contact_blocklist');
    }
}
