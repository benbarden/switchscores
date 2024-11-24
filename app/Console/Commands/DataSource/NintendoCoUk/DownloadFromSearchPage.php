<?php

namespace App\Console\Commands\DataSource\NintendoCoUk;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Domain\DataSource\NintendoCoUk\ImportSearchPage;

class DownloadFromSearchPage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'DSNintendoCoUkDownloadFromSearchPage';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Downloads the N.co.uk search page.';

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

        $scraper = new ImportSearchPage();

        $logger->info('');
        $logger->info('========== IMPORT SEARCH PAGE ==========');

        for ($page = 1; $page < 4; $page++) {
            $items = $scraper->loadHtml($page);
            $logger->info('PAGE '.$page);
            $logger->info(var_export($items, true));
        }

        $logger->info('Complete');
    }
}
