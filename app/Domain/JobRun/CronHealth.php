<?php

namespace App\Domain\JobRun;

use Carbon\Carbon;
use Cron\CronExpression;

/**
 * The state of every nightly job, for the staff home and the Cron health page (#164).
 *
 * Each job in scripts/switchscores-cron gets one of:
 *   success / warning / failed - its latest run since it was last due
 *   running                    - started since it was last due, not finished yet
 *   stuck                      - "running" for longer than STUCK_HOURS (the process died)
 *   missed                     - no run since it was last due, and the grace period is over
 *   pending                    - last due less than GRACE_MINUTES ago, no run yet
 *
 * Times are UTC: the server clock and config/app.php are both UTC.
 */
class CronHealth
{
    const GROUP_KEY = 'cron';

    /** Ben, 2026-10-18 */
    const GRACE_MINUTES = 30;

    const STUCK_HOURS = 2;

    const BAD_STATES = ['failed', 'missed', 'stuck'];

    const WARNING_PREFIX = 'Warning: ';

    public function __construct(
        private CronSchedule $schedule,
        private Repository $repoJobRun,
    )
    {
    }

    /**
     * @return array<int, array{command: string, expression: string, lastDue: Carbon, state: string, run: ?\App\Models\JobRun, warnings: string[]}>
     */
    public function jobs(?Carbon $now = null): array
    {
        $now = $now ?: Carbon::now();
        $jobs = [];

        foreach ($this->schedule->jobs() as $job) {
            $lastDue = Carbon::instance((new CronExpression($job['expression']))->getPreviousRunDate($now, 0, true));
            $run = $this->repoJobRun->getLastByGroupAndCommand(self::GROUP_KEY, $job['command']);

            $jobs[] = [
                'command' => $job['command'],
                'expression' => $job['expression'],
                'lastDue' => $lastDue,
                'state' => $this->state($run, $lastDue, $now),
                'run' => $run,
                'warnings' => $run ? self::warningsFromOutput($run->output) : [],
            ];
        }

        return $jobs;
    }

    public function state(?\App\Models\JobRun $run, Carbon $lastDue, Carbon $now): string
    {
        $ranSinceDue = $run && $run->started_at && $run->started_at->gte($lastDue);

        if ($ranSinceDue) {
            if ($run->status === 'running') {
                return $run->started_at->lt($now->copy()->subHours(self::STUCK_HOURS)) ? 'stuck' : 'running';
            }
            return $run->status;
        }

        if ($now->lt($lastDue->copy()->addMinutes(self::GRACE_MINUTES))) {
            return 'pending';
        }

        return 'missed';
    }

    /**
     * Counts by state, e.g. ['success' => 21, 'warning' => 1, 'missed' => 2].
     */
    public function summary(array $jobs): array
    {
        $counts = [];
        foreach ($jobs as $job) {
            $counts[$job['state']] = ($counts[$job['state']] ?? 0) + 1;
        }

        return $counts;
    }

    /**
     * cron:record writes a command's warnings at the top of its output, one per line.
     */
    public static function formatOutput(array $warnings, ?string $output): string
    {
        $lines = array_map(fn ($w) => self::WARNING_PREFIX.$w, $warnings);

        return ($lines ? implode("\n", $lines)."\n\n" : '').($output ?? '');
    }

    /**
     * @return string[]
     */
    public static function warningsFromOutput(?string $output): array
    {
        $warnings = [];
        foreach (explode("\n", (string) $output) as $line) {
            if (!str_starts_with($line, self::WARNING_PREFIX)) {
                break;
            }
            $warnings[] = substr($line, strlen(self::WARNING_PREFIX));
        }

        return $warnings;
    }
}
