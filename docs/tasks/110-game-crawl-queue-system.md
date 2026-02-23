# 110: Game Crawl POC - Single Game URL Check

## Overview

**This task:** Build a proof-of-concept single-game crawl command that checks URL status. This validates the approach before building the full queue system.

**Future tasks:** Once POC proves the approach, create new tasks for expanding data points, queue infrastructure, and full rollout.

## Scope for #110

- [x] Design doc (this file)
- [x] Add `last_crawled_at` field to games table
- [x] Add `last_crawl_status` field to games table
- [x] Create `game_crawl_lifecycle` table + model
- [x] Build `php artisan game:crawl {game_id} [--save-html]` command
- [x] Build `php artisan game:crawl-batch` command with rate limiting
- [x] Fetch Nintendo UK URL for a game
- [x] Return and log HTTP status code (200, 404, 410, 301, etc.)
- [x] Update `last_crawled_at` on success
- [x] Log to lifecycle table (problems + recoveries only)
- [x] Staff dashboard: crawl lifecycle stats
- [x] Test manually

**Out of scope for #110:** Queue table, batch processing, image downloads, metadata scraping, staff UI.

## Key Context

Around 2023, Nintendo's API broke - coverage dropped from 80-90% of games to just 5-10%. As a workaround, ~8430 games now have manual `nintendo_store_url_override` URLs. These have been sitting unchecked since then, potentially going stale with no automated way to detect broken links. This is the primary driver for the crawl queue system.

## Full Vision (for reference)

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
-- Queue table (only needed for Phase 3+)
CREATE TABLE game_crawl_queue (
    id BIGINT PRIMARY KEY,
    game_id INT UNSIGNED NOT NULL,

    -- Scheduling
    next_crawl_at TIMESTAMP NULL,
    priority TINYINT DEFAULT 5,  -- 0=highest, 10=lowest

    -- Status tracking
    status ENUM('pending', 'in_progress', 'completed', 'failed') DEFAULT 'pending',
    consecutive_failures TINYINT DEFAULT 0,
    last_failure_reason VARCHAR(255) NULL,

    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    FOREIGN KEY (game_id) REFERENCES games(id),
    INDEX idx_next_crawl (next_crawl_at, priority),
    INDEX idx_status (status)
);

-- Note: No separate log table needed.
-- `last_crawled_at` lives on the games table.
-- Audit history via existing audits system.
```

### Games Table Addition
```sql
ALTER TABLE games ADD COLUMN last_crawled_at TIMESTAMP NULL;
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

## Implementation Phases (Revised)

### Phase 1: Single Game Command
1. Add `last_crawled_at` to games table
2. Create `php artisan game:crawl {game_id} [--save-html]` command
3. Implement URL status check (HTTP status code)
4. Test manually on a few games

### Phase 2: Expand Data Points
Once URL check works, add:
- Image URL extraction (#70)
- Player count scraping (#95, #107)
- Publisher scraping (#10)
- Add new fields if needed

### Phase 3: Queue Infrastructure
1. Create `game_crawl_queue` table
2. Create `GameCrawlQueue` model
3. Create `php artisan games:process-crawl-queue --limit=50` command
4. Seed with small test set (e.g., 100 recent games)
5. Validate full cycle

### Phase 4: Full Rollout
1. Seed queue with all games (spread `next_crawl_at` over 60 days)
2. Add cron entry
3. Monitor and adjust

### Phase 5: Staff UI
1. Dashboard showing queue status
2. Manual re-queue button on game detail page
3. Bulk actions (re-queue failed, re-queue by filter)

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

## Questions - RESOLVED

1. **Crawl log table?** No - just `last_crawled_at`. Games already have full audit history.
2. **Store raw HTML?** Only for debugging specific games. Add `--save-html` option for single-game crawl mode. Don't store HTML during mass crawling.
3. **Games with no Nintendo URL?** Can't handle them now. They need either an override URL or a linked DataSource item. Create a list to identify these (<100 games, low impact).
4. **US URL field?** Later.
5. **Manual re-queue trigger?** Staff button only. No auto-trigger on edit. Can bump priority by clearing `last_crawled_at`.

## Pre-requisite: Identify Games Without URLs

Before crawling, need to know which games CAN be crawled. A game needs either:
- `nintendo_store_url_override` field set, OR
- Linked DataSourceParsed item with a URL

Create a staff list or query to identify games with neither (<100 expected). These are out of scope for crawling.

## Iterative Approach

**Key principle:** Start very small. Don't load 15k games into the queue until the crawl-and-save flow is proven.

### Step 1: Single Game Crawl
- Build command: `php artisan game:crawl {game_id} [--save-html]`
- Fetch Nintendo UK page
- Parse ONE piece of data (e.g., URL status or image URL)
- Save to game record
- Update `last_crawled_at`

**Suggested first data point: URL status check**
- Simple: just HTTP status code (200, 404, 410, 301, etc.)
- Immediately useful: finds dead links
- Low risk: doesn't modify game data, just validates

### Step 2: Validate & Expand Data
- Once one data point works reliably, add more (players, publisher, etc.)
- Test on a handful of games manually

### Step 3: Queue Infrastructure
- Only then add the queue table and batch processing
- Start with a small subset (e.g., 100 recently released games)
- Validate the full cycle works

### Step 4: Full Rollout
- Seed queue with all games
- Enable cron schedule

## Notes

- Consider running in off-peak hours (overnight UK time)
- Monitor Nintendo's robots.txt for any restrictions
- User-agent should identify as Switch Scores bot
- Could add CloudFlare caching bypass if needed
