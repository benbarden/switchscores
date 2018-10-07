<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ReviewUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('review_user', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('game_id');
            $table->integer('quick_rating')->nullable();
            $table->decimal('review_score', 4, 1)->nullable();
            $table->text('review_body')->nullable();
            $table->integer('item_status');

            $table->timestamps();

            $table->index('game_id', 'game_id');
            $table->index('user_id', 'user_id');
            $table->index('item_status', 'item_status');
        });

        Schema::create('review_quick_rating', function (Blueprint $table) {
            $table->increments('id');
            $table->string('rating_desc', 20);

            $table->timestamps();
        });

        DB::insert("INSERT INTO review_quick_rating(id, rating_desc, created_at, updated_at) VALUES(1, 'Great', NOW(), NOW())");
        DB::insert("INSERT INTO review_quick_rating(id, rating_desc, created_at, updated_at) VALUES(2, 'Good', NOW(), NOW())");
        DB::insert("INSERT INTO review_quick_rating(id, rating_desc, created_at, updated_at) VALUES(3, 'OK', NOW(), NOW())");
        DB::insert("INSERT INTO review_quick_rating(id, rating_desc, created_at, updated_at) VALUES(4, 'Average', NOW(), NOW())");
        DB::insert("INSERT INTO review_quick_rating(id, rating_desc, created_at, updated_at) VALUES(5, 'Poor', NOW(), NOW())");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('review_user');
        Schema::dropIfExists('review_quick_rating');
    }
}
