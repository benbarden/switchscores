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
        Schema::create('games_company_signups', function (Blueprint $table) {
            $table->increments('id');
            $table->string('contact_name', 50);
            $table->string('contact_role', 50);
            $table->string('contact_email', 255);
            $table->integer('existing_company_id')->nullable();
            $table->string('new_company_name', 100)->nullable();
            $table->string('new_company_type', 20)->nullable();
            $table->string('new_company_url', 255)->nullable();
            $table->string('new_company_twitter', 20)->nullable();
            $table->text('list_of_games')->nullable();

            $table->timestamps();

            $table->index('existing_company_id', 'existing_company_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games_company_signups');
    }
};
