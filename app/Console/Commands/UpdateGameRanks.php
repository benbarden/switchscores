<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use App\Services\Game\RankAllTime;
use App\Services\Game\RankYear;
use App\Services\Game\RankYearMonth;

use App\Domain\GameCalendar\AllowedDates as GameCalendarAllowedDates;

class UpdateGameRanks extends Command
{
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

        // Fix release years
        DB::statement("
            UPDATE games
            SET release_year = YEAR(eu_release_date)
            WHERE eu_release_date IS NOT NULL AND release_year IS NULL
        ");
        DB::statement("
            UPDATE games
            SET release_year = YEAR(eu_release_date)
            WHERE eu_release_date IS NOT NULL AND release_year != YEAR(eu_release_date);
        ");

        // Fix missing digital availability
        // Null values do not get included in the ranks
        DB::statement("
            UPDATE games SET format_digital = 'Available'
            WHERE format_digital IS NULL AND nintendo_store_url_override IS NOT NULL
        ");

        // *** 1. ALL-TIME RANK *** //

        DB::statement("TRUNCATE TABLE game_rank_alltime");

        $serviceRankAllTime = new RankAllTime($logger);
        $serviceRankAllTime->process();

        // *** 2. YEAR RANK *** //

        DB::statement("TRUNCATE TABLE game_rank_year");

        $allowedDates = new GameCalendarAllowedDates();

        $years = $allowedDates->releaseYears(false);

        $serviceRankYear = new RankYear($logger);

        foreach ($years as $year) {

            $serviceRankYear->process($year);

        }

        // *** 3. YEAR/MONTH RANK *** //

        DB::statement("TRUNCATE TABLE game_rank_yearmonth");

        $dateList = $allowedDates->allowedDates(false);

        $serviceRankYearMonth = new RankYearMonth($logger);

        foreach ($dateList as $date) {

            $serviceRankYearMonth->process($date);

        }

    }
}
