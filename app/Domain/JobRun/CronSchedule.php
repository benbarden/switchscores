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
            $job = $this->parseLine($line);
            if ($job) {
                $jobs[] = $job;
            }
        }

        return $jobs;
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
