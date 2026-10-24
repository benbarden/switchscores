<?php

namespace App\Console\Commands;

use App\Domain\JobRun\JobRecorder;
use Illuminate\Console\Command;

/**
 * Runs one nightly job and records it in job_runs (#164):
 *   php artisan cron:record "game:crawl-batch --limit=100 --delay=3"
 *
 * The cron file runs groups through cron:group (#185), which records each step the same
 * way; this stays for a single job and for running one by hand. JobRecorder has the details.
 */
class CronRecord extends Command
{
    protected $signature = 'cron:record {cmd : The artisan command line to run, as one quoted string}';

    protected $description = 'Runs a nightly job and records the run in job_runs for the staff home.';

    public function __construct(private JobRecorder $jobRecorder)
    {
        parent::__construct();
    }

    public function handle()
    {
        @set_time_limit(0);

        return $this->jobRecorder->run($this->argument('cmd')) ? self::SUCCESS : self::FAILURE;
    }
}
