<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SiteAlerts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('site_alerts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('type');
            $table->string('source', 100);
            $table->text('detail');

            $table->timestamps();

            $table->index('type', 'type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('site_alerts');
    }
}
