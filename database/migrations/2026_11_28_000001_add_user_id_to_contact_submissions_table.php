<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserIdToContactSubmissionsTable extends Migration
{
    public function up()
    {
        Schema::table('contact_submissions', function (Blueprint $table) {
            // Member feedback is stored with the contact form messages; this says which member
            // sent it. Null for messages from the public contact form.
            $table->unsignedInteger('user_id')->nullable()->after('id');
            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::table('contact_submissions', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropColumn('user_id');
        });
    }
}
