<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('activity_log', function (Blueprint $table) {
            $table->increments('id');
            $table->string('event_type', 50);
            $table->integer('user_id')->nullable();
            $table->string('event_model', 50)->nullable();
            $table->integer('event_model_id')->nullable();
            $table->text('event_details')->nullable();

            $table->timestamps();

            $table->index('event_type', 'event_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_log');
    }
};
