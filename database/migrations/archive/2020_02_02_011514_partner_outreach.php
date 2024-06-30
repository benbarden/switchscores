<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PartnerOutreach extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('partner_outreach', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('partner_id');
            $table->integer('new_status');
            $table->string('contact_method', 20)->nullable();
            $table->text('contact_message')->nullable();
            $table->text('internal_notes')->nullable();
            $table->timestamps();

            $table->index('partner_id', 'partner_id');
        });

        Schema::table('partners', function(Blueprint $table) {
            $table->integer('last_outreach_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('partners', function(Blueprint $table) {
            $table->dropColumn('last_outreach_id');
        });
        Schema::dropIfExists('partner_outreach');
    }
}
