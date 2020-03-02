<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use App\Traits\SwitchServices;

class UpdateGameRanks extends Command
{
    use SwitchServices;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'UpdateGameRanks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refreshes rank for games.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @throws \Exception
     * @return mixed
     */
    public function handle()
    {
        $logger = Log::channel('cron');

        $logger->info(' *************** '.$this->signature.' *************** ');

        // *** QUICK FIX FOR RELEASE YEARS *** //
        \DB::statement("
            UPDATE games
            SET release_year = YEAR(eu_release_date)
            WHERE eu_release_date IS NOT NULL AND release_year IS NULL
        ");

        // *** 1. ALL-TIME RANK *** //

        \DB::statement("TRUNCATE TABLE game_rank_alltime");

        $gameRankList = \DB::select("
            SELECT g.id AS game_id, g.title, g.rating_avg, g.game_rank
            FROM games g
            WHERE review_count > 2
            ORDER BY rating_avg DESC
        ");

        $logger->info('All-time rank: checking '.count($gameRankList).' games');

        $rankCounter = 1;
        $actualRank = 1;
        $lastRatingAvg = -1;
        $lastRank = -1;

        foreach ($gameRankList as $game) {

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
                \DB::update("UPDATE games SET game_rank = ? WHERE id = ?", [$actualRank, $gameId]);
            }

            $lastRatingAvg = $ratingAvg;
            $lastRank = $actualRank;

            // Save to all-time rank table, if it's in the Top 100.
            // This is faster than storing all the ranks.
            if ($actualRank <= 100) {
                \DB::insert("
                    INSERT INTO game_rank_alltime(game_rank, game_id, created_at, updated_at)
                    VALUES(?, ?, NOW(), NOW())", [$actualRank, $gameId]);
            }

            // This is always incremented by 1
            $rankCounter++;

        }

        // *** 2. YEAR RANK *** //

        \DB::statement("TRUNCATE TABLE game_rank_year");

        $years = $this->getServiceGameCalendar()->getAllowedYears();

        foreach ($years as $year) {

            $gameRankList = \DB::select("
                select g.id AS game_id, g.title, g.rating_avg, g.game_rank
                from games g
                where g.release_year = ?
                and g.review_count > 2
                order by rating_avg desc
            ", [$year]);

            $logger->info('Year rank ['.$year.']: checking '.count($gameRankList).' games');

            $rankCounter = 1;
            $actualRank = 1;
            $lastRatingAvg = -1;
            $lastRank = -1;

            foreach ($gameRankList as $game) {

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

                // Save to year rank table
                \DB::insert("
                    INSERT INTO game_rank_year(release_year, game_rank, game_id, created_at, updated_at)
                    VALUES(?, ?, ?, NOW(), NOW())", array($year, $actualRank, $gameId));

                // This is always incremented by 1
                $rankCounter++;

            }
        }

        // *** 3. YEAR/MONTH RANK *** //

        \DB::statement("TRUNCATE TABLE game_rank_yearmonth");

        $serviceGameCalendar = $this->getServiceGameCalendar();
        $serviceTopRated = $this->getServiceTopRated();

        $dateList = $serviceGameCalendar->getAllowedDates(false);

        foreach ($dateList as $date) {

            $dtDate = new \DateTime($date);
            $dtDateDesc = $dtDate->format('M Y');

            $calendarYear = $dtDate->format('Y');
            $calendarMonth = $dtDate->format('m');

            $yearMonth = $calendarYear.$calendarMonth;

            $gameRatings = $serviceTopRated->getByMonthWithRanks($calendarYear, $calendarMonth);

            $logger->info('Yearmonth ['.$yearMonth.']: checking '.count($gameRatings).' games');

            $rankCounter = 1;
            $actualRank = 1;
            $lastRatingAvg = -1;
            $lastRank = -1;

            foreach ($gameRatings as $game) {

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

                // Save to year rank table
                \DB::insert("
                    INSERT INTO game_rank_yearmonth(release_yearmonth, game_rank, game_id, created_at, updated_at)
                    VALUES(?, ?, ?, NOW(), NOW())
                ", [$yearMonth, $actualRank, $gameId]);

                // This is always incremented by 1
                $rankCounter++;

            }

        }

    }
}
