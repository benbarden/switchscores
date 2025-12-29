<?php


namespace App\Domain\GameRank;

use App\Models\Game;
use Carbon\Carbon;
use Psr\Log\LoggerInterface;
use Illuminate\Support\Facades\DB;

class RankYear
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

    public function getGameList($year)
    {
        return DB::select("
            select g.id AS game_id, g.title, g.rating_avg, g.game_rank
            from games g
            where g.console_id = ?
            and g.release_year = ?
            and g.review_count > 2
            and g.format_digital != ?
            order by rating_avg desc
        ", [$this->consoleId, $year, Game::FORMAT_DELISTED]);
    }

    public function process($year)
    {
        $gameList = $this->getGameList($year);

        //$this->log('info', 'Year rank ['.$year.']: checking '.count($gameList).' games');

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

            $lastRatingAvg = $ratingAvg;
            $lastRank = $actualRank;

            //if ($actualRank > 100) break;

            $rowsToInsert[] = [
                'console_id' => $this->consoleId,
                'release_year' => $year,
                'game_rank' => $actualRank,
                'game_id' => $gameId,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            // This is always incremented by 1
            $rankCounter++;

        }

        // Update table
        DB::table('game_rank_year')->insert($rowsToInsert);
    }
}