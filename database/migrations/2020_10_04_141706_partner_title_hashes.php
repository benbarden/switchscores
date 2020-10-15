<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use Illuminate\Support\Facades\DB;

class PartnerTitleHashes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('partner_title_hashes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title', 150);
            $table->string('title_hash', 64);
            $table->integer('partner_id');
            $table->timestamps();

            $table->index('title_hash', 'title_hash');
            $table->index('partner_id', 'partner_id');
        });

        $partnerList = DB::select('SELECT * FROM partners WHERE type_id = 2 ORDER BY id ASC');
        foreach ($partnerList as $partner) {
            $partnerTitle = strtolower($partner->name);
            $partnerHash = md5($partnerTitle);
            $partnerId = $partner->id;
            DB::insert("
                INSERT INTO partner_title_hashes(title, title_hash, partner_id, created_at, updated_at)
                VALUES(?, ?, ?, NOW(), NOW())
            ", [$partnerTitle, $partnerHash, $partnerId]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('partner_title_hashes');
    }
}
