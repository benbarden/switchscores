<?php


namespace App\Domain\GameRank;

use Illuminate\Support\Facades\DB;
use Psr\Log\LoggerInterface;
use Carbon\Carbon;

use App\Domain\TopRated\DbQueries as DbTopRated;

class RankYearMonth
{
    private $consoleId;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var DbTopRated
     */
    private $dbTopRated;

    /**
     * @param LoggerInterface|null $logger
     */
    public function __construct($consoleId, LoggerInterface $logger = null)
    {
        $this->consoleId = $consoleId;
        if ($logger) {
            $this->logger = $logger;
        }
        $this->dbTopRated = new DbTopRated();
    }

    public function log($level, $message)
    {
        if ($this->logger) {
            $this->logger->{$level}($message);
        }
    }

    public function getGameList($calendarYear, $calendarMonth)
    {
        return $this->dbTopRated->byMonthWithRanks($this->consoleId, $calendarYear, $calendarMonth);
    }

    public function process($date)
    {
        $dtDate = new \DateTime($date);
        $dtDateDesc = $dtDate->format('M Y');

        $calendarYear = $dtDate->format('Y');
        $calendarMonth = $dtDate->format('m');

        $yearMonth = $calendarYear.$calendarMonth;
        $gameList = $this->getGameList($calendarYear, $calendarMonth);

        //$this->log('info', 'Yearmonth ['.$yearMonth.']: checking '.count($gameList).' games');

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

            if ($actualRank > 100) break;

            $rowsToInsert[] = [
                'console_id' => $this->consoleId,
                'release_yearmonth' => $yearMonth,
                'game_rank' => $actualRank,
                'game_id' => $gameId,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            // This is always incremented by 1
            $rankCounter++;

        }

        // Update table
        DB::table('game_rank_yearmonth')->insert($rowsToInsert);
    }
}