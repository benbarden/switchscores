<?php

namespace App\Console\Commands;

use App\Domain\AffiliateCodes\CreatorsApi;
use App\Domain\AffiliateCodes\UsLinkChecker;
use App\Domain\AffiliateCodes\UsLinkCheckTitles;
use App\Domain\Game\Repository\AmazonLinkCheckRepository;
use App\Domain\JobRun\CronRunContext;
use App\Enums\AmazonLinkCheckResult;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Nightly: checks every linked game's Amazon US link still works, and saves what it found, so
 * broken and out-of-stock links show up for staff instead of going stale unnoticed. Run from its
 * own line in scripts/switchscores-cron.
 *
 * Problems with the links themselves aren't a job failure. Only a run that couldn't check links
 * (no credentials, batches refused) warns in cron health.
 */
class AmazonCheckUsLinks extends Command
{
    protected $signature = 'amazon:check-us-links
        {--dry-run : Check and list the problems, but save nothing}';

    protected $description = 'Check linked games\' Amazon US links, and save the result for each game.';

    public function handle(
        UsLinkChecker $checker,
        CreatorsApi $api,
        AmazonLinkCheckRepository $repoLinkCheck,
        UsLinkCheckTitles $titles,
    ): int
    {
        $logger = Log::channel('cron');
        $dryRun = (bool) $this->option('dry-run');

        if (!$api->isConfigured()) {
            $this->warn('The Creators API credentials are not set; nothing checked.');
            CronRunContext::warn('Amazon US link check: the Creators API credentials are not set.');
            return self::SUCCESS;
        }

        $games = $repoLinkCheck->linkedUsWithAsin();
        $check = $checker->check($games);

        $gameIdsByResult = [];
        $titlesByAsin = [];
        foreach ($games as $game) {
            $row = $check['results'][$game->id] ?? null;
            if ($row !== null) {
                $gameIdsByResult[$row['result']->value][] = $game->id;
                $titlesByAsin[$game->amazon_us_asin] = $row['title'];
            }
        }

        if (!$dryRun) {
            $repoLinkCheck->saveUsCheckResults($gameIdsByResult, now());
            $titles->put($titlesByAsin);
        }

        $counts = [];
        foreach (AmazonLinkCheckResult::cases() as $case) {
            $counts[] = $case->label().' '.count($gameIdsByResult[$case->value] ?? []);
        }
        $summary = 'Amazon US link check'.($dryRun ? ' (dry run, nothing saved)' : '').': '
            .count($check['results']).' of '.$games->count().' games checked. '.implode(', ', $counts);
        $this->info($summary);
        $logger->info($summary);

        if ($dryRun) {
            $this->listProblems($games, $check['results']);
        }

        foreach ($check['failedBatches'] as $error) {
            $this->warn($error);
            $logger->warning('Amazon US link check: '.$error);
        }

        if ($check['failedBatches']) {
            CronRunContext::warn('Amazon US link check: '.count($check['failedBatches']).' batch(es) failed, e.g. '.$check['failedBatches'][0]);
        }

        if ($check['asked'] > 0 && !$check['results']) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function listProblems($games, array $results): void
    {
        $rows = [];
        foreach ($games as $game) {
            $row = $results[$game->id] ?? null;
            if ($row === null || $row['result'] === AmazonLinkCheckResult::OK) {
                continue;
            }
            $rows[] = [$row['result']->label(), $game->id, $game->title, $game->amazon_us_asin, $row['title'] ?? ''];
        }

        usort($rows, fn($a, $b) => [$a[0], $a[1]] <=> [$b[0], $b[1]]);

        if ($rows) {
            $this->table(['Result', 'Game ID', 'Game', 'ASIN', 'Amazon title'], $rows);
        }
    }
}
