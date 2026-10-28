<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * signup_alpha and signup_beta were hidden fields on the register form from 2020. Nothing ever
 * filled them in or read them back (empty for every user on localdev, 2026-10-28), so they go.
 *
 * down() puts the columns back empty.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['signup_alpha', 'signup_beta']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('signup_alpha')->nullable();
            $table->text('signup_beta')->nullable();
        });
    }
};
