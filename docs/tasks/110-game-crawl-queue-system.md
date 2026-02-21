# 110: Unified Game Crawl Queue System

## Overview

A queue-based system to crawl Nintendo game pages and maintain data freshness across ~15k games. Instead of batch operations that hammer the server or manual one-off fixes, this provides a sustainable, rate-limited approach to data maintenance.

## Related Tickets

| # | Issue | What crawl solves |
|---|-------|-------------------|
| 70 | Re-download hi-res images | Check image quality, re-download if needed |
| 78 | 404 checker for URLs | Verify URL is live, detect de-listed games |
| 109 | Check for dead Nintendo URLs | Same as #78 |
| 10 | Scrape publisher, players from Nintendo URL | Pull metadata during crawl |
| 95 | Multiplayer options (Local/Online) | Nintendo pages have this data |
| 107 | Store Local vs Online player counts | Part of #95 |
| 108 | Scrape Developer from US pages | Could add US URL crawling |

## Data to Capture Per Crawl

### From UK Nintendo Page (nintendo.co.uk)
- [ ] Page still exists (200 vs 404/410)
- [ ] Hi-res packshot image URL
- [ ] Number of players (string, e.g., "1-4")
- [ ] Players - Local wireless
- [ ] Players - Online
- [ ] Publisher name
- [ ] Release date (verify/update)
- [ ] Price (if shown)
- [ ] File size
- [ ] Supported languages
- [ ] Game description (for reference, not to copy)

### From US Nintendo Page (nintendo.com) - Future
- [ ] Developer name (not on UK pages)
- [ ] ESRB rating
- [ ] Any US-specific data

### Derived Data
- [ ] Image quality assessment (dimensions, file size)
- [ ] URL status (live, redirect, 404, 410)
- [ ] Data completeness score

## Proposed Schema

```sql
-- Main queue table
CREATE TABLE game_crawl_queue (
    id BIGINT PRIMARY KEY,
    game_id INT UNSIGNED NOT NULL,

    -- Scheduling
    last_crawled_at TIMESTAMP NULL,
    next_crawl_at TIMESTAMP NULL,
    priority TINYINT DEFAULT 5,  -- 0=highest, 10=lowest

    -- Status tracking
    status ENUM('pending', 'in_progress', 'completed', 'failed') DEFAULT 'pending',
    consecutive_failures TINYINT DEFAULT 0,
    last_failure_reason VARCHAR(255) NULL,

    -- What to check on next crawl (flags)
    check_url_status BOOLEAN DEFAULT TRUE,
    check_image BOOLEAN DEFAULT TRUE,
    check_metadata BOOLEAN DEFAULT TRUE,

    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    FOREIGN KEY (game_id) REFERENCES games(id),
    INDEX idx_next_crawl (next_crawl_at, priority),
    INDEX idx_status (status)
);

-- Crawl history/results (optional, for debugging)
CREATE TABLE game_crawl_log (
    id BIGINT PRIMARY KEY,
    game_id INT UNSIGNED NOT NULL,
    crawled_at TIMESTAMP,

    url_status SMALLINT,  -- HTTP status code
    image_updated BOOLEAN DEFAULT FALSE,
    metadata_updated BOOLEAN DEFAULT FALSE,

    raw_response TEXT NULL,  -- Store for debugging if needed
    error_message VARCHAR(255) NULL,

    FOREIGN KEY (game_id) REFERENCES games(id),
    INDEX idx_game_date (game_id, crawled_at)
);
```

## Architecture Options

### Option A: Scheduled Artisan Command (No Supervisor)

Simple approach - run via cron every few hours.

```php
// app/Console/Commands/ProcessGameCrawlQueue.php
class ProcessGameCrawlQueue extends Command
{
    protected $signature = 'games:crawl {--limit=50}';

    public function handle()
    {
        $games = GameCrawlQueue::where('next_crawl_at', '<=', now())
            ->where('status', '!=', 'in_progress')
            ->orderBy('priority')
            ->orderBy('next_crawl_at')
            ->limit($this->option('limit'))
            ->get();

        foreach ($games as $queueItem) {
            $this->crawlGame($queueItem);
            sleep(2); // Rate limiting between requests
        }
    }
}
```

**Cron:** `0 */3 * * * php artisan games:crawl --limit=50`

**Pros:**
- No supervisor needed
- Simple to understand and debug
- Easy to run manually

**Cons:**
- Sequential processing (slower)
- If command dies mid-way, need to handle recovery

### Option B: Laravel Queue with Database Driver (No Supervisor)

Use Laravel's built-in queue with database driver, process via cron.

```php
// Dispatch jobs
GameCrawlJob::dispatch($game)->delay(now()->addSeconds($index * 2));

// Process via cron
* * * * * php artisan queue:work database --stop-when-empty --max-time=300
```

**Pros:**
- Built-in retry logic
- Jobs are atomic
- Can scale up later if needed

**Cons:**
- Slightly more complex
- Still sequential without supervisor

### Option C: Queue with Supervisor (Full Solution)

Most robust but requires supervisor setup.

**Pros:**
- Parallel processing
- Auto-restart on failure
- Production-ready

**Cons:**
- Supervisor config needed
- More moving parts

### Recommendation

Start with **Option A** (scheduled command). It's the simplest, requires no new infrastructure, and handles 50-100 games every few hours perfectly well. Can upgrade to Option B/C later if needed.

## Implementation Phases

### Phase 1: Foundation
1. Create migrations for `game_crawl_queue` table
2. Create `GameCrawlQueue` model
3. Seed queue with all games (initial `next_crawl_at` spread over 60 days)
4. Create basic `ProcessGameCrawlQueue` command
5. Add cron entry

### Phase 2: URL Health Check (#78, #109)
1. Implement URL status checking
2. Update game's URL status field
3. Handle 404s (mark as potentially de-listed)
4. Handle redirects (update URL if permanent)

### Phase 3: Image Quality (#70)
1. Check current image dimensions
2. Compare with Nintendo page image
3. Re-download if higher quality available
4. Update game record

### Phase 4: Metadata Scraping (#10, #95, #107)
1. Scrape player counts (Local/Online)
2. Scrape publisher
3. Store in appropriate game fields
4. Add new fields if needed for Local/Online players

### Phase 5: Staff UI
1. Dashboard showing queue status
2. Manual re-queue buttons
3. View crawl history per game
4. Bulk actions (re-queue category, re-queue failed, etc.)

## Rate Limiting Strategy

- **Requests per batch:** 50-100
- **Batch frequency:** Every 2-3 hours
- **Daily total:** 200-400 requests
- **Full cycle:** 40-75 days to crawl all 15k games
- **Delay between requests:** 2-5 seconds

## Priority Weighting

| Priority | Description |
|----------|-------------|
| 0 | Manual re-queue (staff action) |
| 1 | New game (never crawled) |
| 2 | Failed crawl retry |
| 3 | Ranked game refresh |
| 4 | Recent release (last 90 days) |
| 5 | Standard (default) |
| 8 | Low quality game |
| 10 | Very old / inactive game |

## Error Handling

- **Temporary failure (timeout, 5xx):** Increment `consecutive_failures`, retry in 24h
- **Permanent failure (404, 410):** Mark game URL as dead, flag for review
- **3+ consecutive failures:** Lower priority, flag for manual review
- **Rate limited (429):** Pause queue, alert, backoff

## Questions to Resolve

1. Do we need the full crawl log table, or just last_crawled_at?
2. Should we store raw HTML responses for debugging?
3. How to handle games with no Nintendo URL?
4. Add US URL field to games table now, or later?
5. What triggers a manual re-queue? Game edit? Staff button?

## Notes

- Consider running in off-peak hours (overnight UK time)
- Monitor Nintendo's robots.txt for any restrictions
- User-agent should identify as Switch Scores bot
- Could add CloudFlare caching bypass if needed
