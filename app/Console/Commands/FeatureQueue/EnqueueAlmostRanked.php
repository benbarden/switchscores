<?php

namespace App\Console\Commands\FeatureQueue;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Enums\FeatureQueueBucket;

class EnqueueAlmostRanked extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'features:enqueue
        {--bucket= : Bucket slug}
        {--min-score=7.5}
        {--cooldown-days=120}
        {--refresh : Clear unused entries in this bucket before enqueueing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enqueue candidates into feature_queue for the chosen bucket.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $minScore = (float) $this->option('min-score');
        $cooldown = (int) $this->option('cooldown-days');

        $bucket = FeatureQueueBucket::tryFromSlug($this->option('bucket'));
        if (!$bucket) {
            $this->error('Invalid bucket slug.');
            return self::FAILURE;
        }

        $bucketSlug = $bucket->value;

        // --refresh: clear only unused rows in this bucket
        if ($this->option('refresh')) {
            $deleted = DB::table('feature_queue')
                ->where('bucket', $bucketSlug)
                ->whereNull('used_at')
                ->delete();
            $this->info("Refreshed queue: removed {$deleted} unused rows for '{$bucketSlug}'.");
        }

        // derive conditions per bucket
        [$reviewWhere, $notes] = match ($bucketSlug) {
            FeatureQueueBucket::NEEDS_2_REVIEWS->value => ["g.review_count = 2", 'needs one more review'],
            FeatureQueueBucket::NEEDS_1_REVIEW->value  => ["g.review_count = 1", 'has 1 review'],
            FeatureQueueBucket::NEEDS_0_REVIEWS->value => ["(g.review_count IS NULL OR g.review_count = 0)", 'no reviews yet'],
            default => [null, null],
        };

        if ($reviewWhere === null) {
            $this->error('This command currently supports only needs-* buckets.');
            return self::FAILURE;
        }

        // Adjust column names if different in your schema:
        // games.review_count, games.avg_score, games.quality_flag
        $sql = "
            INSERT IGNORE INTO feature_queue (game_id, bucket, priority, notes)
            SELECT g.id,
                   '{$bucketSlug}' AS bucket,
                   (
                     (g.rating_avg * 10)                                   -- base weight from score
                     + (CASE g.review_count WHEN 2 THEN 5 ELSE 0 END)      -- bonus for 2 reviews
                     + (CASE
                          WHEN g.eu_release_date IS NOT NULL
                          THEN GREATEST(0, 200 - DATEDIFF(CURDATE(), g.eu_release_date)) / 25
                          ELSE 0
                        END)                                               -- boost for recency
                   ) AS priority,
                   '{$notes}' AS notes
            FROM games g
            WHERE g.is_low_quality = 0
              AND g.format_digital <> 'De-listed'
              AND {$reviewWhere}
              AND g.rating_avg >= :minScore
              AND NOT EXISTS (
                  SELECT 1 FROM feature_queue fq
                  WHERE fq.game_id = g.id
                    AND fq.bucket = '{$bucketSlug}'
                    AND fq.used_at IS NOT NULL
                    AND fq.used_at >= DATE_SUB(CURDATE(), INTERVAL :cooldown DAY)
              )
            ORDER BY g.eu_release_date DESC
        ";

        $inserted = DB::affectingStatement($sql, [
            'minScore' => $minScore,
            'cooldown' => $cooldown,
        ]);

        $this->info("Enqueue complete: {$inserted} row(s) added.");
        return self::SUCCESS;
    }
}
