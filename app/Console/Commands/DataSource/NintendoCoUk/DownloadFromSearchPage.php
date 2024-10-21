<?php

namespace App\Console\Commands\DataSource\NintendoCoUk;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Domain\DataSource\NintendoCoUk\ImportSearchPage;

use App\Traits\SwitchServices;

class DownloadFromSearchPage extends Command
{
    use SwitchServices;

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
        $html = $scraper->downloadHtml();
        print($html);

        $logger->info('Complete');
    }
}
