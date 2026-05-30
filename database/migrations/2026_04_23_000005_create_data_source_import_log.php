<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDataSourceImportLog extends Migration
{
    public function up()
    {
        Schema::create('data_source_import_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('source_id');
            $table->string('link_id', 50);
            $table->unsignedInteger('game_id')->nullable();
            $table->string('event_type', 20); // added, updated, delisted, conflict
            $table->json('changed_fields')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['source_id', 'created_at']);
            $table->index(['link_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('data_source_import_log');
    }
}
