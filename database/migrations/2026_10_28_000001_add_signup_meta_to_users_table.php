<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Details of the signup request (IP, country, user agent, time taken to submit the form and so
 * on), written once at registration, so bogus signups can be spotted by their patterns. One JSON
 * column rather than a column per detail, so what's recorded can change without a migration.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('signup_meta')->nullable()->after('verification_email_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('signup_meta');
        });
    }
};
