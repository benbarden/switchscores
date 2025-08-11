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
        Schema::create('job_runs', function (Blueprint $t) {
            $t->id();
            $t->string('group_key');
            $t->string('command');
            $t->string('status')->default('queued'); // queued|running|success|failed
            $t->integer('exit_code')->nullable();
            $t->timestamp('queued_at')->nullable();
            $t->timestamp('started_at')->nullable();
            $t->timestamp('finished_at')->nullable();
            $t->integer('duration_ms')->nullable();
            $t->text('output')->nullable(); // last N chars is fine to store
            $t->timestamps();
            $t->index(['group_key','command','status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_runs');
    }
};
