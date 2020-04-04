<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DataSourceIgnoreList extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('data_source_ignore', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('source_id');
            $table->integer('link_id')->nullable();
            $table->string('title', 255)->nullable();
            $table->timestamps();

            $table->index('source_id', 'source_id');
            $table->index('link_id', 'link_id');
        });

        DB::statement("
            INSERT INTO data_source_ignore(source_id, link_id)
            SELECT '2', fs_id FROM eshop_europe_ignore;
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('data_source_ignore');
    }
}
