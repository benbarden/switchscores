<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDataSourceImportRuns extends Migration
{
    public function up()
    {
        Schema::create('data_source_import_runs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('source_id');
            $table->string('status', 20)->default('running'); // running, completed, failed
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();

            $table->index(['source_id', 'started_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('data_source_import_runs');
    }
}
