<?php

namespace App\Domain\JobRun;

use Illuminate\Support\Facades\Artisan;

use App\Models\JobRun;

/**
 * Runs one job and records it in job_runs (#164). Used by cron:record for a single
 * job and by cron:group for each step of a group (#185), so every step gets its own row.
 *
 * A nightly run is saved with group_key 'cron' - status, exit code, duration and output - so
 * the staff home can say whether last night's jobs ran. Nobody reads the cron log (Ben,
 * 2026-10-18), so this is how a failed or missed job gets seen.
 *
 * The Tools Hub's runs come through here too (#214), under group_key 'tools', so a manual
 * run gets the same warnings and Sentry reporting without counting as a nightly run.
 *
 * Exceptions are recorded, then reported, so Sentry emails exactly as it did before the
 * wrapper. A command can also raise a warning through CronRunContext::warn().
 *
 * Runs the command in-process via Artisan::call().
 */
class JobRecorder
{
    /** Ben, 2026-10-18 */
    const RETENTION_DAYS = 60;

    const TOOLS_GROUP_KEY = 'tools';

    public function __construct(private Repository $repoJobRun)
    {
    }

    /**
     * @return bool  true if the command exited 0 (a warning still counts as success)
     */
    public function run(string $command, string $groupKey = CronHealth::GROUP_KEY): bool
    {
        $jobRun = $this->repoJobRun->createRunningJob($groupKey, trim($command));

        return $this->record($jobRun);
    }

    /**
     * Runs a row that already exists. The Tools Hub creates it as queued on the click, and
     * the queue worker runs it here when it picks the job up.
     */
    public function runExisting(JobRun $jobRun): bool
    {
        $this->repoJobRun->markAsRunning($jobRun);

        return $this->record($jobRun);
    }

    private function record(JobRun $jobRun): bool
    {
        CronRunContext::start();
        $started = hrtime(true);

        try {
            $exitCode = Artisan::call($jobRun->command);
            $output = Artisan::output();
        } catch (\Throwable $e) {
            CronRunContext::finish();
            $this->repoJobRun->markAsFailed($jobRun, get_class($e).': '.$e->getMessage());
            report($e);
            $this->prune($jobRun->group_key);
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

        $this->prune($jobRun->group_key);

        return $isSuccessful;
    }

    private function prune(string $groupKey): void
    {
        $this->repoJobRun->deleteByGroupStartedBefore($groupKey, now()->subDays(self::RETENTION_DAYS));
    }
}
