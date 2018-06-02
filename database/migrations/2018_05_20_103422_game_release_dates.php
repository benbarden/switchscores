<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GameReleaseDates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('game_release_dates', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('game_id');
            $table->string('region', 2);
            $table->date('release_date')->nullable();
            $table->tinyInteger('is_released');
            $table->string('upcoming_date', 30)->nullable();
            $table->integer('release_year')->nullable();

            $table->timestamps();

            $table->index('game_id', 'game_id');
        });

        DB::insert("
            INSERT INTO game_release_dates(
              game_id, region, release_date, is_released, upcoming_date, release_year, created_at, updated_at
            )
            SELECT id, 'eu', release_date,
            CASE WHEN upcoming = 1 THEN 0 ELSE 1 END,
            upcoming_date, release_year,
            NOW(), NOW()
            FROM games ORDER BY id ASC
        ");

        DB::insert("
            INSERT INTO game_release_dates(
              game_id, region, release_date, is_released, upcoming_date, release_year, created_at, updated_at
            )
            SELECT id, 'us', NULL, 0, NULL, NULL,
            NOW(), NOW()
            FROM games ORDER BY id ASC
        ");

        DB::insert("
            INSERT INTO game_release_dates(
              game_id, region, release_date, is_released, upcoming_date, release_year, created_at, updated_at
            )
            SELECT id, 'jp', NULL, 0, NULL, NULL,
            NOW(), NOW()
            FROM games ORDER BY id ASC
        ");

        DB::update("
            ALTER TABLE `games`
            CHANGE COLUMN `release_date` `zzz_release_date` DATE NULL DEFAULT NULL ,
            CHANGE COLUMN `upcoming` `zzz_upcoming` TINYINT(1) NOT NULL DEFAULT '0' ,
            CHANGE COLUMN `upcoming_date` `zzz_upcoming_date` VARCHAR(30) NULL DEFAULT NULL ,
            CHANGE COLUMN `release_year` `zzz_release_year` VARCHAR(255) NULL DEFAULT NULL ;
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('game_release_dates');

        DB::update("
            ALTER TABLE `games`
            CHANGE COLUMN `zzz_release_date` `release_date` DATE NULL DEFAULT NULL ,
            CHANGE COLUMN `zzz_upcoming` `upcoming` TINYINT(1) NOT NULL DEFAULT '0' ,
            CHANGE COLUMN `zzz_upcoming_date` `upcoming_date` VARCHAR(30) NULL DEFAULT NULL ,
            CHANGE COLUMN `zzz_release_year` `release_year` VARCHAR(255) NULL DEFAULT NULL ;
        ");
    }
}
