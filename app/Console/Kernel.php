<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\EshopEuropeImportData::class,
        Commands\EshopEuropeLinkGames::class,
        Commands\EshopEuropeUpdateNintendoUrls::class,
        Commands\RunFeedImporter::class,
        Commands\RunFeedParser::class,
        Commands\RunFeedReviewGenerator::class,
        Commands\UpdateGameCalendarStats::class,
        Commands\UpdateGameReviewStats::class,
        Commands\UpdateGameImageCount::class,
        Commands\UpdateGameRanks::class,
        Commands\WikipediaCrawlGamesList::class,
        Commands\WikipediaImportGamesList::class,
        Commands\WikipediaUpdateGamesList::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
