<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApiRequestLogTable extends Migration
{
    public function up()
    {
        Schema::create('api_request_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('api_version', 10)->nullable()->index();
            $table->string('method', 10);
            $table->string('path')->index();
            $table->unsignedSmallInteger('status_code')->nullable();
            $table->unsignedBigInteger('token_id')->nullable()->index();
            $table->string('ip', 45)->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->timestamps();

            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('api_request_log');
    }
}
