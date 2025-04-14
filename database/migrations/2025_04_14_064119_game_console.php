<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('games', function(Blueprint $table) {
            $table->integer('console_id')->default(1)->after('link_title');
            $table->index('console_id', 'console_id');
        });

        Schema::create('consoles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 30);

            $table->timestamps();
        });

        DB::insert("INSERT INTO consoles(id, name) VALUES(1, 'Switch 1')");
        DB::insert("INSERT INTO consoles(id, name) VALUES(2, 'Switch 2')");
        DB::update("UPDATE games SET console_id = 1");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consoles');

        Schema::table('games', function(Blueprint $table) {
            $table->dropColumn('console_id');
        });
    }
};
