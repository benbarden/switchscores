<?php

namespace App\Console\Commands\FeatureQueue;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Enums\FeatureQueueBucket;

class FeatureQueueEnqueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'features:enqueue
        {--bucket= : Bucket slug}
        {--min-score=7.5}
        {--min-steam-score=7 : Minimum Steam review_score (0-9) for unranked-steam-gem bucket}
        {--category-id= : Category ID to scope the unranked-steam-gem bucket}
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
        $minScore     = (float) $this->option('min-score');
        $minSteamScore = (int) $this->option('min-steam-score');
        $cooldown     = (int) $this->option('cooldown-days');
        $categoryId   = $this->option('category-id') ? (int) $this->option('category-id') : null;

        $bucket = FeatureQueueBucket::tryFromSlug($this->option('bucket'));
        if (!$bucket) {
            $this->error('Invalid bucket slug.');
            return self::FAILURE;
        }

        $bucketSlug = $bucket->value;

        // --refresh: clear only unused rows in this bucket (scoped to category if provided)
        if ($this->option('refresh')) {
            $deleteQuery = DB::table('feature_queue')
                ->where('bucket', $bucketSlug)
                ->whereNull('used_at');
            if ($categoryId) {
                $deleteQuery->where('category_id', $categoryId);
            }
            $deleted = $deleteQuery->delete();
            $this->info("Refreshed queue: removed {$deleted} unused rows for '{$bucketSlug}'.");
        }

        if ($bucketSlug === FeatureQueueBucket::UNRANKED_STEAM_GEM->value) {
            return $this->enqueueSteamGems($bucketSlug, $categoryId, $minSteamScore, $cooldown);
        }

        // derive conditions per bucket
        [$reviewWhere, $notes] = match ($bucketSlug) {
            FeatureQueueBucket::HAS_2_REVIEWS->value => ["g.review_count = 2", 'has 2 reviews'],
            FeatureQueueBucket::HAS_1_REVIEW->value  => ["g.review_count = 1", 'has 1 review'],
            FeatureQueueBucket::HAS_0_REVIEWS->value => ["(g.review_count IS NULL OR g.review_count = 0)", 'no reviews yet'],
            default => [null, null],
        };

        if ($reviewWhere === null) {
            $this->error('Unsupported bucket.');
            return self::FAILURE;
        }

        $sql = "
            INSERT IGNORE INTO feature_queue (game_id, bucket, priority, notes)
            SELECT g.id,
                   '{$bucketSlug}' AS bucket,
                   (
                     (g.rating_avg * 10)
                     + (CASE g.review_count WHEN 2 THEN 5 ELSE 0 END)
                     + (CASE
                          WHEN g.eu_release_date IS NOT NULL
                          THEN GREATEST(0, 200 - DATEDIFF(CURDATE(), g.eu_release_date)) / 25
                          ELSE 0
                        END)
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

    private function enqueueSteamGems(string $bucketSlug, ?int $categoryId, int $minSteamScore, int $cooldown): int
    {
        $categoryFilter = $categoryId ? "AND g.category_id = {$categoryId}" : '';

        // Deduplicate by steam_id: one queue entry per unique Steam game per category.
        // If two game records (S1 + S2) share a steam_id, pick the one with the lower id.
        $sql = "
            INSERT IGNORE INTO feature_queue (game_id, bucket, category_id, priority, notes)
            SELECT g.id,
                   '{$bucketSlug}' AS bucket,
                   " . ($categoryId ? $categoryId : 'NULL') . " AS category_id,
                   (
                     (srd.review_score * 10)
                     + LOG(GREATEST(srd.total_reviews, 1))
                   ) AS priority,
                   CONCAT('Steam: ', srd.review_score_desc, ' (', srd.total_reviews, ' reviews)') AS notes
            FROM games g
            JOIN steam_review_data srd ON srd.game_id = g.id
            WHERE g.is_low_quality = 0
              AND g.game_status = 'active'
              AND g.steam_status = 'linked'
              AND (g.review_count IS NULL OR g.review_count < 3)
              AND g.game_rank IS NULL
              AND srd.review_score >= :minSteamScore
              {$categoryFilter}
              AND g.id = (
                  SELECT MIN(g2.id)
                  FROM games g2
                  WHERE g2.steam_id = g.steam_id
                    AND g2.is_low_quality = 0
                    AND g2.game_status = 'active'
              )
              AND NOT EXISTS (
                  SELECT 1 FROM feature_queue fq
                  WHERE fq.game_id = g.id
                    AND fq.bucket = '{$bucketSlug}'
                    AND fq.used_at IS NOT NULL
                    AND fq.used_at >= DATE_SUB(CURDATE(), INTERVAL :cooldown DAY)
              )
            ORDER BY priority DESC
        ";

        $inserted = DB::affectingStatement($sql, [
            'minSteamScore' => $minSteamScore,
            'cooldown'      => $cooldown,
        ]);

        $this->info("Enqueue complete: {$inserted} row(s) added for '{$bucketSlug}'" .
            ($categoryId ? " (category {$categoryId})" : '') . '.');

        return self::SUCCESS;
    }
}
