<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use Illuminate\Support\Facades\DB;

class GameCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100);
            $table->string('link_title', 100);
            $table->integer('parent_id')->nullable();
            $table->timestamps();

            $table->index('link_title', 'link_title');
            $table->index('parent_id', 'parent_id');
        });

        Schema::table('games', function(Blueprint $table) {
            $table->integer('category_id')->after('link_title')->nullable();
            $table->index('category_id', 'category_id');
        });

        Schema::table('tags', function(Blueprint $table) {
            $table->integer('category_id')->nullable();
            $table->index('category_id', 'category_id');
        });

        DB::insert('
            INSERT INTO categories(id, name, link_title, created_at, updated_at)
            SELECT id, primary_type, link_title, created_at, updated_at FROM game_primary_types
            ORDER BY id ASC
        ');

        DB::update('
            UPDATE games SET category_id = primary_type_id WHERE primary_type_id IS NOT NULL
        ');

        DB::update('
            UPDATE tags SET category_id = primary_type_id WHERE primary_type_id IS NOT NULL
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tags', function(Blueprint $table) {
            $table->dropColumn('category_id');
        });

        Schema::table('games', function(Blueprint $table) {
            $table->dropColumn('category_id');
        });

        Schema::dropIfExists('categories');
    }
}
