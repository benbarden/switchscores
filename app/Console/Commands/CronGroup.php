<?php

namespace App\Console\Commands;

use App\Domain\JobRun\JobRecorder;
use Illuminate\Console\Command;

/**
 * Runs a group of nightly jobs in order (#185):
 *   php artisan cron:group games
 *
 * The groups are in config/cron-groups.php - adding a job to a group is one line there. Each
 * step is recorded on its own in job_runs (JobRecorder, as cron:record does), so cron health
 * shows every step, not just the group. The group itself isn't recorded.
 *
 * A failed step doesn't stop the group: it's recorded and reported, and the next step runs.
 * The group exits 1 if any step failed.
 */
class CronGroup extends Command
{
    protected $signature = 'cron:group {name : A group in config/cron-groups.php}';

    protected $description = 'Runs a group of nightly jobs in order, recording each one in job_runs.';

    public function __construct(private JobRecorder $jobRecorder)
    {
        parent::__construct();
    }

    public function handle()
    {
        @set_time_limit(0);

        $name = $this->argument('name');
        $steps = config('cron-groups.'.$name);
        if (!is_array($steps) || !$steps) {
            $this->error('Unknown cron group: '.$name);
            return self::FAILURE;
        }

        $failed = [];
        foreach ($steps as $step) {
            $this->info('Running '.$step);
            if (!$this->jobRecorder->run($step)) {
                $failed[] = $step;
            }
        }

        if ($failed) {
            $this->error('Failed: '.implode(', ', $failed));
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
