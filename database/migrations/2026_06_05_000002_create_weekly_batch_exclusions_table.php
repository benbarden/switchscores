<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWeeklyBatchExclusionsTable extends Migration
{
    public function up()
    {
        Schema::create('weekly_batch_exclusions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title', 255);
            $table->string('console', 20);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['title', 'console']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('weekly_batch_exclusions');
    }
}
