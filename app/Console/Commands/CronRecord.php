<?php

namespace App\Console\Commands;

use App\Domain\JobRun\CronHealth;
use App\Domain\JobRun\CronRunContext;
use App\Domain\JobRun\Repository as JobRunRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

/**
 * Runs one nightly job and records it in job_runs (#164).
 *
 * Every line in scripts/switchscores-cron goes through this:
 *   php artisan cron:record "DSNintendoCoUkUpdateGames"
 * The run is saved with group_key 'cron' - status, exit code, duration and output - so the
 * staff home can say whether last night's jobs ran. Nobody reads the cron log (Ben,
 * 2026-10-18), so this is how a failed or missed job gets seen.
 *
 * Exceptions are recorded, then reported, so Sentry emails exactly as it did before the
 * wrapper. A command can also raise a warning through CronRunContext::warn().
 *
 * Runs the command in-process via Artisan::call(), as the Tools Hub's RunArtisanCommand
 * job already does for the same commands.
 */
class CronRecord extends Command
{
    /** Ben, 2026-10-18 */
    const RETENTION_DAYS = 60;

    protected $signature = 'cron:record {cmd : The artisan command line to run, as one quoted string}';

    protected $description = 'Runs a nightly job and records the run in job_runs for the staff home.';

    public function __construct(private JobRunRepository $repoJobRun)
    {
        parent::__construct();
    }

    public function handle()
    {
        @set_time_limit(0);

        $command = trim($this->argument('cmd'));
        $jobRun = $this->repoJobRun->createRunningJob(CronHealth::GROUP_KEY, $command);

        CronRunContext::start();
        $started = hrtime(true);

        try {
            $exitCode = Artisan::call($command);
            $output = Artisan::output();
        } catch (\Throwable $e) {
            CronRunContext::finish();
            $this->repoJobRun->markAsFailed($jobRun, get_class($e).': '.$e->getMessage());
            report($e);
            $this->prune();
            return self::FAILURE;
        }

        $ended = hrtime(true);
        $warnings = CronRunContext::finish();

        $isSuccessful = ($exitCode === 0);
        $this->repoJobRun->markAsComplete(
            $jobRun, $isSuccessful, $started, $ended, $exitCode,
            CronHealth::formatOutput($warnings, $output)
        );
        if ($isSuccessful && $warnings) {
            $this->repoJobRun->markAsWarning($jobRun);
        }

        $this->prune();

        return $isSuccessful ? self::SUCCESS : self::FAILURE;
    }

    private function prune(): void
    {
        $this->repoJobRun->deleteByGroupStartedBefore(CronHealth::GROUP_KEY, now()->subDays(self::RETENTION_DAYS));
    }
}
