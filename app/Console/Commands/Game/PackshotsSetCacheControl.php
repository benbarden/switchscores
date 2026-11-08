<?php

namespace App\Console\Commands\Game;

use App\Domain\Game\ImageResolver;
use App\Domain\Game\PackshotCacheControl;
use Aws\S3\Exception\S3Exception;
use Illuminate\Console\Command;

/**
 * Sets the year-long Cache-Control header on packshot objects already in the bucket.
 * A dry run unless --apply is given. See PackshotCacheControl for how and why.
 */
class PackshotsSetCacheControl extends Command
{
    const BATCH_SIZE = 100;
    const LIST_LIMIT = 20;

    protected $signature = 'packshots:set-cache-control
        {--apply : Change the objects. Without it, only reports what would change}
        {--limit= : Stop after this many objects updated (or, in a dry run, that would be)}
        {--pause=200 : Milliseconds to wait after every '.self::BATCH_SIZE.' objects checked}';

    protected $description = 'Sets the year-long Cache-Control header on existing packshot objects';

    public function __construct(private PackshotCacheControl $cacheControl)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $limit = $this->option('limit') !== null ? (int) $this->option('limit') : null;
        $pauseMs = max(0, (int) $this->option('pause'));

        if ($limit !== null && $limit < 1) {
            $this->error('--limit must be 1 or more.');
            return self::FAILURE;
        }

        $this->info($apply
            ? 'APPLY - objects will be updated'
            : 'DRY RUN - nothing will be changed (add --apply to update)');
        $this->line('Header: '.ImageResolver::CACHE_CONTROL);
        $this->newLine();

        $counts = [
            PackshotCacheControl::RESULT_UPDATED => 0,
            PackshotCacheControl::RESULT_WOULD_UPDATE => 0,
            PackshotCacheControl::RESULT_ALREADY_SET => 0,
            PackshotCacheControl::RESULT_MISSING => 0,
            PackshotCacheControl::RESULT_NO_CONTENT_TYPE => 0,
            'failed' => 0,
        ];
        $listed = ['missing' => [], 'no content type' => [], 'failed' => []];
        $checked = 0;

        foreach ($this->cacheControl->keys() as $key) {
            try {
                $result = $this->cacheControl->update($key, $apply);
            } catch (S3Exception $e) {
                $result = 'failed';
                $this->remember($listed['failed'], $key.' ('.($e->getAwsErrorCode() ?: $e->getStatusCode()).')');
            }

            $counts[$result]++;
            if ($result === PackshotCacheControl::RESULT_MISSING || $result === PackshotCacheControl::RESULT_NO_CONTENT_TYPE) {
                $this->remember($listed[$result], $key);
            }

            $checked++;
            if ($checked % 1000 === 0) {
                $this->line("Checked {$checked}...");
            }

            $changed = $counts[PackshotCacheControl::RESULT_UPDATED] + $counts[PackshotCacheControl::RESULT_WOULD_UPDATE];
            if ($limit !== null && $changed >= $limit) {
                $this->line("Stopped at the limit of {$limit}.");
                break;
            }

            if ($pauseMs > 0 && $checked % self::BATCH_SIZE === 0) {
                usleep($pauseMs * 1000);
            }
        }

        $this->newLine();
        $this->table(['Result', 'Objects'], collect($counts)
            ->filter(fn ($count, $result) => $count > 0 || $result === ($apply ? 'updated' : 'would update'))
            ->map(fn ($count, $result) => [$result, $count])
            ->values()
            ->all());
        $this->line("Checked: {$checked}");

        foreach ($listed as $result => $keys) {
            if (!$keys) {
                continue;
            }
            $this->newLine();
            $this->warn(ucfirst($result).' (first '.self::LIST_LIMIT.'):');
            foreach ($keys as $key) {
                $this->line('  '.$key);
            }
        }

        return $counts['failed'] > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function remember(array &$list, string $item): void
    {
        if (count($list) < self::LIST_LIMIT) {
            $list[] = $item;
        }
    }
}
