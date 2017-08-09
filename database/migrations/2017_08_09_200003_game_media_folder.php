<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GameMediaFolder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('games', function(Blueprint $table) {
            $table->string('media_folder', '100')->nullable();
        });

        // Zelda BOTW
        DB::update("UPDATE games SET media_folder = '/img/media/zelda-botw/' WHERE id = 1");
        // Images:
        // id: 1 to 13
        // 000 -> 012-zelda.jpg
        $imageCount = 13;
        $firstId = 1;
        for ($i=0; $i<$imageCount; $i++) {
            $imgFileNo = $i;
            $imgRecordId = $i + $firstId;
            $imgFileName = str_pad($imgFileNo, 3, '0', STR_PAD_LEFT).'-zelda.jpg';
            DB::update("UPDATE game_images SET url = '$imgFileName' WHERE id = $imgRecordId");
        }

        // Blaster Master Zero
        DB::update("UPDATE games SET media_folder = '/img/media/blaster-master-zero/' WHERE id = 19");
        // Images:
        // id: 14 to 20
        // 001 -> 007-blaster-master-zero.jpg
        $imageCount = 7;
        $firstId = 14;
        $gameFileSuffix = 'blaster-master-zero';
        for ($i=0; $i<$imageCount; $i++) {
            $imgFileNo = $i + 1;
            $imgRecordId = $i + $firstId;
            $imgFileName = str_pad($imgFileNo, 3, '0', STR_PAD_LEFT).'-'.$gameFileSuffix.'.jpg';
            DB::update("UPDATE game_images SET url = '$imgFileName' WHERE id = $imgRecordId");
        }

        // Graceful Explosion Machine
        DB::update("UPDATE games SET media_folder = '/img/media/graceful-explosion-machine/' WHERE id = 30");
        // Images:
        // id: 21 to 31
        // 001 -> 011
        $imageCount = 11;
        $firstId = 21;
        $gameFileSuffix = 'graceful-explosion-machine';
        for ($i=0; $i<$imageCount; $i++) {
            $imgFileNo = $i + 1;
            $imgRecordId = $i + $firstId;
            $imgFileName = str_pad($imgFileNo, 3, '0', STR_PAD_LEFT).'-'.$gameFileSuffix.'.jpg';
            DB::update("UPDATE game_images SET url = '$imgFileName' WHERE id = $imgRecordId");
        }

        // Lego City Undercover
        DB::update("UPDATE games SET media_folder = '/img/media/lego-city-undercover/' WHERE id = 31");
        // Images:
        // id: 32 to 52
        // 001 -> 021
        $imageCount = 21;
        $firstId = 32;
        $gameFileSuffix = 'lego-city-undercover';
        for ($i=0; $i<$imageCount; $i++) {
            $imgFileNo = $i + 1;
            $imgRecordId = $i + $firstId;
            $imgFileName = str_pad($imgFileNo, 3, '0', STR_PAD_LEFT).'-'.$gameFileSuffix.'.jpg';
            DB::update("UPDATE game_images SET url = '$imgFileName' WHERE id = $imgRecordId");
        }

        // Super Bomberman R
        DB::update("UPDATE games SET media_folder = '/img/media/super-bomberman-r/' WHERE id = 3");
        // Images:
        // id: 53 to 82
        // 001 -> 030
        $imageCount = 30;
        $firstId = 53;
        $gameFileSuffix = 'super-bomberman-r';
        for ($i=0; $i<$imageCount; $i++) {
            $imgFileNo = $i + 1;
            $imgRecordId = $i + $firstId;
            $imgFileName = str_pad($imgFileNo, 3, '0', STR_PAD_LEFT).'-'.$gameFileSuffix.'.jpg';
            DB::update("UPDATE game_images SET url = '$imgFileName' WHERE id = $imgRecordId");
        }

        // Shovel Knight
        DB::update("UPDATE games SET media_folder = '/img/media/shovel-knight/' WHERE id = 8");
        // Images:
        // id: 83 to 96
        // 001 -> 014
        $imageCount = 14;
        $firstId = 83;
        $gameFileSuffix = 'shovel-knight';
        for ($i=0; $i<$imageCount; $i++) {
            $imgFileNo = $i + 1;
            $imgRecordId = $i + $firstId;
            $imgFileName = str_pad($imgFileNo, 3, '0', STR_PAD_LEFT).'-'.$gameFileSuffix.'.jpg';
            DB::update("UPDATE game_images SET url = '$imgFileName' WHERE id = $imgRecordId");
        }

        // Cave Story
        DB::update("UPDATE games SET media_folder = '/img/media/cave-story/' WHERE id = 158");
        // Images:
        // id: 97 to 105
        // 000 -> 008
        $imageCount = 9;
        $firstId = 97;
        $gameFileSuffix = 'cave-story';
        for ($i=0; $i<$imageCount; $i++) {
            $imgFileNo = $i; // 000
            $imgRecordId = $i + $firstId;
            $imgFileName = str_pad($imgFileNo, 3, '0', STR_PAD_LEFT).'-'.$gameFileSuffix.'.jpg';
            DB::update("UPDATE game_images SET url = '$imgFileName' WHERE id = $imgRecordId");
        }

        // Ironcast
        DB::update("UPDATE games SET media_folder = '/img/media/ironcast/' WHERE id = 139");
        // Images:
        // id: 106 to 112
        // 1 -> 7
        $imageCount = 7;
        $firstId = 106;
        $gameFilePrefix = 'ironcast-ripstone';
        for ($i=0; $i<$imageCount; $i++) {
            $imgFileNo = $i + 1;
            $imgRecordId = $i + $firstId;
            $imgFileName = $gameFilePrefix.'-'.$imgFileNo.'.jpg';
            DB::update("UPDATE game_images SET url = '$imgFileName' WHERE id = $imgRecordId");
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('games', function(Blueprint $table) {
            $table->dropColumn('media_folder');
        });
    }
}
