<?php

namespace App\Domain\JobRun;

use Illuminate\Support\Facades\Artisan;

/**
 * Runs one nightly job and records it in job_runs (#164). Used by cron:record for a single
 * job and by cron:group for each step of a group (#185), so every step gets its own row.
 *
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
class JobRecorder
{
    /** Ben, 2026-10-18 */
    const RETENTION_DAYS = 60;

    public function __construct(private Repository $repoJobRun)
    {
    }

    /**
     * @return bool  true if the command exited 0 (a warning still counts as success)
     */
    public function run(string $command): bool
    {
        $command = trim($command);
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
            return false;
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

        return $isSuccessful;
    }

    private function prune(): void
    {
        $this->repoJobRun->deleteByGroupStartedBefore(CronHealth::GROUP_KEY, now()->subDays(self::RETENTION_DAYS));
    }
}
