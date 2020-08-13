<?php


namespace App\Services\Game;

use Illuminate\Support\Facades\DB;
use Illuminate\Log\Logger;
use Carbon\Carbon;

class RankAllTime
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param Logger|null $logger
     */
    public function __construct(Logger $logger = null)
    {
        if ($logger) {
            $this->logger = $logger;
        }
    }

    public function log($level, $message)
    {
        if ($this->logger) {
            $this->logger->{$level}($message);
        }
    }

    public function getGameList()
    {
        return DB::select("
            SELECT g.id AS game_id, g.title, g.rating_avg, g.game_rank
            FROM games g
            WHERE review_count > 2
            ORDER BY rating_avg DESC
        ");
    }

    public function process()
    {
        $gameList = $this->getGameList();

        $this->log('info', 'All-time rank: checking '.count($gameList).' games');

        $rankCounter = 1;
        $actualRank = 1;
        $lastRatingAvg = -1;
        $lastRank = -1;

        $rowsToInsert = [];

        $now = Carbon::now('utc')->toDateTimeString();

        foreach ($gameList as $game) {

            $gameId = $game->game_id;
            $gameTitle = $game->title;
            $ratingAvg = $game->rating_avg;
            $prevRank = $game->game_rank;

            if ($lastRatingAvg == -1) {
                // First record
                $actualRank = $rankCounter;
            } elseif ($lastRatingAvg == $ratingAvg) {
                // Same as previous rank
                $actualRank = $lastRank;
            } else {
                // Go to next rank
                $actualRank = $rankCounter;
            }

            // Save rank to DB
            if ($actualRank != $prevRank) {
                DB::update("UPDATE games SET game_rank = ? WHERE id = ?", [$actualRank, $gameId]);
            }

            $lastRatingAvg = $ratingAvg;
            $lastRank = $actualRank;

            // Save to all-time rank table, if it's in the Top 100.
            // This is faster than storing all the ranks.
            if ($actualRank > 100) break;

            $rowsToInsert[] = [
                'game_rank' => $actualRank,
                'game_id' => $gameId,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            DB::insert("
                INSERT INTO game_rank_alltime(game_rank, game_id, created_at, updated_at)
                VALUES(?, ?, NOW(), NOW())", [$actualRank, $gameId]);

            // This is always incremented by 1
            $rankCounter++;

        }

        // Update table
        DB::table('game_rank_alltime')->insert($rowsToInsert);
    }
}