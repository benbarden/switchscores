<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DataSources extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('data_sources', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 30);
            $table->tinyInteger('import_method')->nullable();
            $table->tinyInteger('is_active');
            $table->timestamps();
        });

        Schema::create('data_source_raw', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('source_id');
            $table->string('title', 255)->nullable();
            $table->mediumText('source_data_json')->nullable();
            $table->timestamps();

            $table->index('source_id', 'source_id');
        });

        Schema::create('data_source_parsed', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('source_id');
            $table->integer('game_id')->nullable();
            $table->string('title', 255)->nullable();
            $table->string('price', 20)->nullable();
            $table->date('release_date')->nullable();
            $table->string('developers', 255)->nullable();
            $table->string('publishers', 255)->nullable();
            $table->timestamps();

            $table->index('source_id', 'source_id');
            $table->index('game_id', 'game_id');
        });

        DB::insert("INSERT INTO data_sources(name, import_method, is_active, created_at, updated_at) VALUES('Switch eShop - UK', 9, 1, NOW(), NOW())");
        DB::insert("INSERT INTO data_sources(name, import_method, is_active, created_at, updated_at) VALUES('Nintendo.co.uk', 1, 1, NOW(), NOW())");
        DB::insert("INSERT INTO data_sources(name, import_method, is_active, created_at, updated_at) VALUES('Nintendo.com', NULL, 1, NOW(), NOW())");
        DB::insert("INSERT INTO data_sources(name, import_method, is_active, created_at, updated_at) VALUES('Wikipedia', 2, 1, NOW(), NOW())");
        DB::insert("INSERT INTO data_sources(name, import_method, is_active, created_at, updated_at) VALUES('whattoplay', NULL, 1, NOW(), NOW())");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('data_sources');
        Schema::dropIfExists('data_source_raw');
        Schema::dropIfExists('data_source_parsed');
    }
}
