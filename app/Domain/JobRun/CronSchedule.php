<?php

namespace App\Domain\JobRun;

/**
 * Reads the expected nightly jobs from scripts/switchscores-cron (#164).
 *
 * The cron file in the repo is the schedule: there's no second list to keep in step.
 * If the file on the server is older than the repo's (it's installed by hand), the jobs
 * it lacks show as missed, which is the point.
 */
class CronSchedule
{
    public function __construct(
        private ?string $path = null
    )
    {
        $this->path = $path ?? base_path('scripts/switchscores-cron');
    }

    /**
     * @return array<int, array{expression: string, command: string}>
     */
    public function jobs(): array
    {
        if (!is_readable($this->path)) {
            return [];
        }

        $jobs = [];
        foreach (file($this->path, FILE_IGNORE_NEW_LINES) as $line) {
            $jobs = array_merge($jobs, $this->parseLineJobs($line));
        }

        return $jobs;
    }

    /**
     * The groups in config/cron-groups.php in the order the cron file runs them (the file is in
     * time order), then any group the file doesn't schedule. The Tools Hub's tab order.
     *
     * @return string[]
     */
    public function groupOrder(): array
    {
        $scheduled = [];
        if (is_readable($this->path)) {
            foreach (file($this->path, FILE_IGNORE_NEW_LINES) as $line) {
                $line = trim($line);
                if ($line !== '' && !str_starts_with($line, '#') && preg_match('/php artisan cron:group ([\w-]+)/', $line, $m)) {
                    $scheduled[] = $m[1];
                }
            }
        }

        $groups = array_keys(config('cron-groups') ?? []);
        $scheduled = array_values(array_intersect(array_unique($scheduled), $groups));

        return array_merge($scheduled, array_values(array_diff($groups, $scheduled)));
    }

    /**
     * The jobs on one cron line. A cron:group line (#185) is one job per step, each due at the
     * group's time, since cron:group records every step on its own:
     *   30 5 * * * www-data cd /var/www/switchscores.com && php artisan cron:group games >/dev/null 2>&1
     * Any other line is at most one job (parseLine). An unknown group has none.
     *
     * @return array<int, array{expression: string, command: string}>
     */
    public function parseLineJobs(string $line): array
    {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            return [];
        }

        $parts = preg_split('/\s+/', $line, 7);
        if (count($parts) === 7 && preg_match('/php artisan cron:group ([\w-]+)/', $parts[6], $m)) {
            $expression = implode(' ', array_slice($parts, 0, 5));
            $steps = array_keys(config('cron-groups.'.$m[1].'.steps') ?? []);
            return array_map(fn ($step) => ['expression' => $expression, 'command' => $step], $steps);
        }

        $job = $this->parseLine($line);
        return $job ? [$job] : [];
    }

    /**
     * One /etc/cron.d line: five schedule fields, the user, then the command, e.g.
     *   15 4 * * * www-data cd /var/www/switchscores.com && php artisan cron:record "X" >/dev/null 2>&1
     * The bare form (php artisan X) is read too, so a line not yet wrapped still counts.
     */
    public function parseLine(string $line): ?array
    {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            return null;
        }

        $parts = preg_split('/\s+/', $line, 7);
        if (count($parts) < 7) {
            return null;
        }

        $expression = implode(' ', array_slice($parts, 0, 5));
        $rest = $parts[6];

        if (preg_match('/php artisan cron:record "([^"]+)"/', $rest, $m)) {
            $command = $m[1];
        } elseif (preg_match('/php artisan (.+?)\s*(?:>|$)/', $rest, $m)) {
            $command = $m[1];
        } else {
            return null;
        }

        return ['expression' => $expression, 'command' => trim($command)];
    }
}
