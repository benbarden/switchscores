<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Traits\SwitchServices;

class LoggerTest extends Command
{
    use SwitchServices;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'LoggerTest';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test logging command.';

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
        $logger->debug('Test debug');

        $logger = Log::channel('dev-debug');
        $logger->info('Dev debug logger');
    }
}
