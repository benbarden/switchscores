<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EshopIgnoreList extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eshop_europe_ignore', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('fs_id');
            $table->timestamps();

            $table->index('fs_id', 'fs_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('eshop_europe_ignore');
    }
}
