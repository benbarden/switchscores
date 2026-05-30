<?php


namespace App\Domain\GameRank;

use App\Enums\GameStatus;
use App\Models\Game;
use Carbon\Carbon;
use Psr\Log\LoggerInterface;
use Illuminate\Support\Facades\DB;

class RankAllTime
{
    private $consoleId;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param integer
     * @param LoggerInterface|null $logger
     */
    public function __construct($consoleId, LoggerInterface $logger = null)
    {
        $this->consoleId = $consoleId;
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
            WHERE console_id = ?
            AND review_count > 2
            AND game_status = ?
            ORDER BY rating_avg DESC
        ", [$this->consoleId, GameStatus::ACTIVE->value]);
    }

    public function process()
    {
        $consoleId = $this->consoleId;

        $gameList = $this->getGameList();

        $this->log('info', 'All-time rank: checking '.count($gameList).' games');

        $this->log('info', 'Clearing previous ranks');
        DB::update('UPDATE games SET game_rank = NULL WHERE console_id = ?', [$consoleId]);

        $this->log('info', 'Calculating new ranks');

        $rankCounter = 1;
        $actualRank = 1;
        $lastRatingAvg = -1;
        $lastRank = -1;

        $rowsToInsert = [];

        $now = Carbon::now('utc')->toDateTimeString();

        foreach ($gameList as $game) {

            $gameId = $game->game_id;
            $ratingAvg = $game->rating_avg;

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
            //DB::update("UPDATE games SET game_rank = ? WHERE id = ?", [$actualRank, $gameId]);

            $lastRatingAvg = $ratingAvg;
            $lastRank = $actualRank;

            // Save all the game ranks regardless of Top 500 etc
            // Much quicker than update game_rank row by row - we can do it in one go.
            $rowsToInsert[] = [
                'console_id' => $consoleId,
                'game_rank' => $actualRank,
                'game_id' => $gameId,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            // This is always incremented by 1
            $rankCounter++;

        }

        // Update table
        $this->log('info', 'Updating table: game_rank_alltime');
        DB::table('game_rank_alltime')->insert($rowsToInsert);

        // Update games
        $this->log('info', 'Updating game_rank field on games');
        DB::update('
            update games g, game_rank_alltime gra
            set g.game_rank = gra.game_rank
            where g.id = gra.game_id and gra.game_id is not null
        ');
    }
}