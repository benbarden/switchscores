<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use App\Traits\SwitchServices;

use App\Services\Game\RankAllTime;
use App\Services\Game\RankYear;
use App\Services\Game\RankYearMonth;

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
        DB::statement("
            UPDATE games
            SET release_year = YEAR(eu_release_date)
            WHERE eu_release_date IS NOT NULL AND release_year IS NULL
        ");

        // *** 1. ALL-TIME RANK *** //

        DB::statement("TRUNCATE TABLE game_rank_alltime");

        $serviceRankAllTime = new RankAllTime($logger);
        $serviceRankAllTime->process();

        // *** 2. YEAR RANK *** //

        DB::statement("TRUNCATE TABLE game_rank_year");

        $years = $this->getServiceGameCalendar()->getAllowedYears();

        $serviceRankYear = new RankYear($logger);

        foreach ($years as $year) {

            $serviceRankYear->process($year);

        }

        // *** 3. YEAR/MONTH RANK *** //

        DB::statement("TRUNCATE TABLE game_rank_yearmonth");

        $serviceGameCalendar = $this->getServiceGameCalendar();

        $dateList = $serviceGameCalendar->getAllowedDates(false);

        $serviceRankYearMonth = new RankYearMonth($logger, $this->getServiceTopRated());

        foreach ($dateList as $date) {

            $serviceRankYearMonth->process($date);

        }

    }
}
