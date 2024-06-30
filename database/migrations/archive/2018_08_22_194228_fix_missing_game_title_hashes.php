<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\Services\GameTitleHashService;

class FixMissingGameTitleHashes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $serviceGameTitleHash = new GameTitleHashService();

        $gamesWithNoTitleHashes = \DB::select("
            SELECT g.id AS game_id, g.title, count(gth.id) AS hash_count
            FROM games g
            LEFT JOIN game_title_hashes gth ON gth.game_id = g.id
            GROUP BY g.id
            HAVING hash_count = 0
            ORDER BY hash_count ASC, g.id;
        ");

        if ($gamesWithNoTitleHashes) {

            foreach ($gamesWithNoTitleHashes as $gameToUpdate) {

                $gameId = $gameToUpdate->game_id;
                $gameTitle = $gameToUpdate->title;

                $titleHash = $serviceGameTitleHash->generateHash($gameTitle);
                $existingTitleHash = $serviceGameTitleHash->getByHash($titleHash);

                $gameTitleHash = $serviceGameTitleHash->create($gameTitle, $titleHash, $gameId);

            }

        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
