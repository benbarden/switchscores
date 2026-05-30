<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWeeklyBatchesTable extends Migration
{
    public function up()
    {
        Schema::create('weekly_batches', function (Blueprint $table) {
            $table->id();
            $table->date('batch_date');
            $table->string('status', 20)->default('setup'); // setup, in_progress, complete
            $table->timestamps();

            $table->unique('batch_date');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('weekly_batches');
    }
}
