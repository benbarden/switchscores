<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Domain\GameCalendar\Stats as GameCalendarStats;
use App\Domain\GameCalendar\AllowedDates as GameCalendarAllowedDates;

class UpdateGameCalendarStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'UpdateGameCalendarStats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates the released game stats for the Release Calendar.';

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

        $statsGameCalendar = new GameCalendarStats;
        $datesGameCalendar = new GameCalendarAllowedDates;

        $dateList = $datesGameCalendar->allowedDates(false);

        \DB::statement('TRUNCATE TABLE game_calendar_stats');

        foreach ($dateList as $date) {

            $dtDate = new \DateTime($date);
            $dtDateDesc = $dtDate->format('M Y');

            $calendarYear = $dtDate->format('Y');
            $calendarMonth = $dtDate->format('m');

            $monthCount = $statsGameCalendar->calendarStatCount($calendarYear, $calendarMonth);

            $logger->info($date.' // '.$monthCount.' game(s)');

            \DB::insert('
                INSERT INTO game_calendar_stats(month_name, released_count, created_at, updated_at)
                VALUES(?, ?, NOW(), NOW())
            ', [$date, $monthCount]);

        }

    }
}
