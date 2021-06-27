<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\IntegrityCheck;

class IntegrityChecks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('integrity_checks', function (Blueprint $table) {
            $table->increments('id');
            $table->string('check_name', 100);
            $table->text('description')->nullable();
            $table->string('entity_name', 100);
            $table->tinyInteger('is_passing')->nullable();
            $table->integer('failing_count')->default(0)->nullable();

            $table->timestamps();
            $table->index('check_name', 'check_name');
        });

        Schema::create('integrity_check_results', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('check_id');
            $table->tinyInteger('is_passing');
            $table->integer('failing_count')->default(0);
            $table->text('id_list')->nullable();

            $table->timestamps();
            $table->index('check_id', 'check_id');
        });

        DB::insert("
            INSERT INTO integrity_checks(check_name, description, entity_name)
            VALUES(?, ?, ?)
        ", [IntegrityCheck::GAME_WRONG_RELEASE_YEAR, IntegrityCheck::DESC_GAME_WRONG_RELEASE_YEAR, 'App\Game']);

        DB::insert("
            INSERT INTO integrity_checks(check_name, description, entity_name)
            VALUES(?, ?, ?)
        ", [IntegrityCheck::GAME_NO_RELEASE_YEAR, IntegrityCheck::DESC_GAME_NO_RELEASE_YEAR, 'App\Game']);

        DB::insert("
            INSERT INTO integrity_checks(check_name, description, entity_name)
            VALUES(?, ?, ?)
        ", [IntegrityCheck::GAME_MISSING_RANK, IntegrityCheck::DESC_GAME_MISSING_RANK, 'App\Game']);

        DB::insert("
            INSERT INTO integrity_checks(check_name, description, entity_name)
            VALUES(?, ?, ?)
        ", [IntegrityCheck::GAME_NO_TITLE_HASHES, IntegrityCheck::DESC_GAME_NO_TITLE_HASHES, 'App\Game']);

        DB::insert("
            INSERT INTO integrity_checks(check_name, description, entity_name)
            VALUES(?, ?, ?)
        ", [IntegrityCheck::GAME_TITLE_HASH_MISMATCH, IntegrityCheck::DESC_GAME_TITLE_HASH_MISMATCH, 'App\Game']);

        DB::insert("
            INSERT INTO integrity_checks(check_name, description, entity_name)
            VALUES(?, ?, ?)
        ", [IntegrityCheck::TITLE_HASH_NO_GAME_MATCH, IntegrityCheck::DESC_TITLE_HASH_NO_GAME_MATCH, 'App\GameTitleHash']);

        DB::insert("
            INSERT INTO integrity_checks(check_name, description, entity_name)
            VALUES(?, ?, ?)
        ", [IntegrityCheck::REVIEW_LINK_DUPLICATE, IntegrityCheck::DESC_REVIEW_LINK_DUPLICATE, 'App\ReviewLink']);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('integrity_checks');
        Schema::dropIfExists('integrity_check_results');
    }
}
