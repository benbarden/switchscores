<?php

namespace App\Console\Commands\DataSource\NintendoCoUk;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Domain\DataSource\NintendoCoUk\UpdateGame;

class UpdateAvailability extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'DSNintendoCoUkUpdateAvailability';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Quick fix to update the availability of eShop Europe data records.';

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
     * @return mixed
     */
    public function handle()
    {
        $logger = Log::channel('cron');

        $logger->info(' *************** '.$this->signature.' *************** ');

        $updateGame = new UpdateGame();

        $logger->info('Updating games to Available ... ');

        $updateGame->updateDigitalAvailable();

        $logger->info('Updating games to De-listed ... ');

        $updateGame->updateDigitalDelisted();

        $logger->info('Complete');
    }
}
