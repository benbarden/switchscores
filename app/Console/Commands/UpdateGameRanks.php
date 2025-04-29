<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use App\Domain\GameRank\RankAllTime;
use App\Domain\GameRank\RankYear;
use App\Domain\GameRank\RankYearMonth;
use App\Domain\Console\Repository as ConsoleRepository;

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

        // Setup

        $repoConsole = new ConsoleRepository();
        $consoleList = $repoConsole->consoleList();
        $allowedDates = new GameCalendarAllowedDates();

        $logger->info('Truncating tables');
        DB::statement("TRUNCATE TABLE game_rank_alltime");
        DB::statement("TRUNCATE TABLE game_rank_year");
        DB::statement("TRUNCATE TABLE game_rank_yearmonth");

        // Loop through consoles

        foreach ($consoleList as $console) {

            $consoleId = $console['id'];

            $logger->info(sprintf('Processing console: %s [id: %s]', $console['name'], $console['id']));

            // *** 1. ALL-TIME RANK *** //
            $domainRankAllTime = new RankAllTime($consoleId, $logger);
            $domainRankAllTime->process();

            // *** 2. YEAR RANK *** //
            $years = $allowedDates->releaseYearsByConsole($consoleId, false);
            $domainRankYear = new RankYear($consoleId, $logger);
            $logger->info('Updating table: game_rank_year');
            foreach ($years as $year) {
                $domainRankYear->process($year);
            }

            // *** 3. YEAR/MONTH RANK *** //
            $dateList = $allowedDates->allowedDatesByConsole($consoleId, false);
            $domainRankYearMonth = new RankYearMonth($consoleId, $logger);
            $logger->info('Updating table: game_rank_yearmonth');
            foreach ($dateList as $date) {
                $domainRankYearMonth->process($date);
            }

        }

        $logger->info('Complete');

    }
}
