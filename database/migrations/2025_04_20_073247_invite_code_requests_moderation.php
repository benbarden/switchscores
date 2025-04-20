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
        Schema::table('invite_code_requests', function(Blueprint $table) {
            $table->string('status', 30);
        });
        DB::update("UPDATE invite_code_requests SET status = 'Pending'");

        Schema::create('invite_code_deny_list', function (Blueprint $table) {
            $table->increments('id');
            $table->string('deny_item', 100);
            $table->string('deny_type', 20);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invite_code_requests', function(Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::dropIfExists('invite_code_config');
    }
};
