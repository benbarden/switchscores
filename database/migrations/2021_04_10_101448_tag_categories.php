<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TagCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::update('TRUNCATE TABLE game_tags');
        DB::update('TRUNCATE TABLE tags');

        Schema::table('tags', function(Blueprint $table) {
            $table->dropColumn('category_id');
            $table->integer('tag_category_id')->nullable();
        });

        Schema::create('tag_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100);
            $table->string('link_title', 100);
            $table->integer('category_order')->nullable();
            $table->timestamps();

            $table->index('link_title', 'link_title');
        });

        DB::insert("
            INSERT INTO tag_categories(id, name, link_title, category_order, created_at, updated_at)
            VALUES(1, 'Genres and sub-genres', 'genres-and-sub-genres', 10, NOW(), NOW())
        ");
        DB::insert("
            INSERT INTO tag_categories(id, name, link_title, category_order, created_at, updated_at)
            VALUES(2, 'Gameplay', 'gameplay', 20, NOW(), NOW())
        ");
        DB::insert("
            INSERT INTO tag_categories(id, name, link_title, category_order, created_at, updated_at)
            VALUES(3, 'Setting', 'setting', 30, NOW(), NOW())
        ");
        DB::insert("
            INSERT INTO tag_categories(id, name, link_title, category_order, created_at, updated_at)
            VALUES(4, 'Story', 'story', 40, NOW(), NOW())
        ");
        DB::insert("
            INSERT INTO tag_categories(id, name, link_title, category_order, created_at, updated_at)
            VALUES(5, 'Content', 'content', 50, NOW(), NOW())
        ");
        DB::insert("
            INSERT INTO tag_categories(id, name, link_title, category_order, created_at, updated_at)
            VALUES(6, 'Mood', 'mood', 60, NOW(), NOW())
        ");
        DB::insert("
            INSERT INTO tag_categories(id, name, link_title, category_order, created_at, updated_at)
            VALUES(7, 'Visual style', 'visual-style', 70, NOW(), NOW())
        ");
        DB::insert("
            INSERT INTO tag_categories(id, name, link_title, category_order, created_at, updated_at)
            VALUES(8, 'Time mechanic', 'time-mechanic', 80, NOW(), NOW())
        ");
        DB::insert("
            INSERT INTO tag_categories(id, name, link_title, category_order, created_at, updated_at)
            VALUES(9, 'Difficulty', 'difficulty', 90, NOW(), NOW())
        ");
        DB::insert("
            INSERT INTO tag_categories(id, name, link_title, category_order, created_at, updated_at)
            VALUES(10, 'Audience', 'audience', 100, NOW(), NOW())
        ");
        DB::insert("
            INSERT INTO tag_categories(id, name, link_title, category_order, created_at, updated_at)
            VALUES(11, 'Retrogaming era', 'retrogaming-era', 110, NOW(), NOW())
        ");
        DB::insert("
            INSERT INTO tag_categories(id, name, link_title, category_order, created_at, updated_at)
            VALUES(12, 'Scoring', 'scoring', 120, NOW(), NOW())
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tag_categories');

        Schema::table('tags', function(Blueprint $table) {
            $table->dropColumn('tag_category_id');
            $table->integer('category_id')->nullable();
        });
    }
}
