# #132 — Steam-backed News Content (Editorial Auto-generation)

## Overview

Surface unranked Switch games that have positive Steam reviews as auto-generated editorial content under `/news`. Games with 0–2 Switch Scores reviews currently have no `rating_avg` signal, but Steam review data provides a usable quality signal. Posts are generated per category (e.g. "Metroidvanias loved on Steam"), giving readers focused, genre-specific editorial content.

The editorial angle: *"These Switch games haven't been reviewed much here yet, but Steam players love them."*

---

## Background

### Existing system (already built)
- `FeatureQueueBucket` enum — `HAS_2_REVIEWS`, `HAS_1_REVIEW`, `HAS_0_REVIEWS`, `NEWLY_RANKED`, `FORGOTTEN_GEM`
- `feature_queue` table — priority scoring, cooldown logic
- `features:enqueue` artisan command — filters games, calculates priority, inserts into queue
- `generateBucketDraft()` in `DashboardController` — auto-generates a draft news post from a queue
- `news_post_games` junction table — links news posts back to featured games
- Steam data: `steam_review_data` table (`review_score`, `total_positive`, `total_negative`, `total_reviews`), `steam_status` enum on `games` table

### What's missing
The existing buckets score games by `rating_avg`, which is null for 0-review games. A Steam bucket needs a different priority signal drawn from `steam_review_data`. The existing dashboard uses hard-coded content types; a dedicated page is needed to manage per-category Steam gem queues across all categories.

---

## Proposed Implementation

### 1. New enum case

Add to `App\Enums\FeatureQueueBucket`:

```php
UNRANKED_STEAM_GEM = 'unranked-steam-gem'
```

### 2. Migration — add `category_id` to `feature_queue`

Add a nullable `category_id` foreign key to the `feature_queue` table so queue entries can be scoped per category:

```php
$table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
```

The unique constraint on `(bucket, game_id)` should be updated to `(bucket, game_id, category_id)` to allow the same game to appear in multiple category queues.

### 3. Extend `features:enqueue` command

Handle the new bucket with:
- New `--category-id` option — stores `category_id` on queue entries
- Filter: `review_count < 3`, `steam_status = linked`, join `steam_review_data`
- Minimum quality threshold: `review_score >= 7` (Mostly Positive) to start; configurable via `--min-steam-score`
- Priority calculation: `(review_score * 10) + log(total_reviews)` — weights sentiment + volume
- Cooldown: same mechanism as existing buckets (`--cooldown-days`)

### 4. New page: `/staff/news/steam-gems`

A dedicated management page (separate from the existing news dashboard) showing a table with one row per category:

| Category | Potential | In Queue | Used |
|---|---|---|---|
| Metroidvania | 14 | 8 | 3 |
| RPG | 31 | 0 | 0 |

Column definitions:
- **Potential** — games with `steam_status = linked`, `review_count < 3`, not currently in the queue or in cooldown. Tells you how many more could be enqueued right now.
- **In Queue** — enqueued entries with `used_at IS NULL` for this bucket + category
- **Used** — entries with `used_at IS NOT NULL` (already featured in a post)

Actions per row:
- **Enqueue** — triggers `features:enqueue --bucket=unranked-steam-gem --category-id=X`
- **Generate Draft** — active only when In Queue ≥ threshold (e.g. 10); calls `generateBucketDraft()` with category filter

Only categories with at least 1 Potential or In Queue game need to be shown (exclude empty rows).

The existing bucket drill-down at `/staff/news/bucket/{bucket}` remains as the per-category game list view.

### 5. Update `generateBucketDraft()`

- Accept a `category_id` filter — scopes the queue query to that category
- Include Steam sentiment per game in the generated HTML: e.g. "Very Positive — 1,200 reviews" alongside the standard `[gameheader]` / `[gameblurb]` shortcodes
- Post title: e.g. "Metroidvania Switch games loved on Steam — May 2026"
- Post category: featured games (ID 8)

### 6. New routes

```
GET  /staff/news/steam-gems                          → steam-gems index page
POST /staff/news/steam-gems/{categoryId}/enqueue     → trigger enqueue for category
POST /staff/news/steam-gems/{categoryId}/generate    → generate draft for category
```

---

## Decisions Log

| # | Decision |
|---|----------|
| 1 | Use existing `generateBucketDraft()` pattern rather than a new generation path. Extend to accept category filter and Steam-aware HTML. |
| 2 | Minimum Steam score threshold: `review_score >= 7` (Mostly Positive) to start, given sparse coverage. Raise to 8 (Very Positive) once pool is deeper. |
| 3 | No console split. S1 and S2 variants of the same game share a Steam ID. Deduplicate by `steam_id` at enqueue and draft generation time. If both console variants are present, show the game once in the post and note both consoles are available. |
| 4 | Category-scoped queue: add `category_id` to `feature_queue` rather than filtering at generation time. Enables accurate per-category counts on the steam-gems page. |
| 5 | Dedicated `/staff/news/steam-gems` page rather than extending the existing dashboard. The existing dashboard's hard-coded switch statement doesn't scale to 30–50 categories. |
| 6 | Post threshold: ~10 games in queue before generating a post. |
| 7 | If a subcategory doesn't have enough games to reach the threshold, pulling from the parent category + all its subcategories is a stage 2 enhancement. |
| 8 | Queue entries are marked as used at draft creation, not at publish time. This prevents overlapping drafts from drawing the same games. The news editor should show a notice making this clear. |
| 9 | "Reset and delete draft" button in the news editor for Steam gem posts. Clears `used_at` on the linked queue entries (via `news_post_games`) before deleting the post, so games return to the ready pool. Not automated on delete — must be an explicit action. |
| 10 | Delete-after-publish does not restore games to the queue. The cooldown period applies; manual reset via the bucket view is the escape hatch if needed. |

---

## Notes

- Steam coverage is currently sparse — filling the queue requires manually checking Steam IDs via `/staff/reviews/steam-links` (Unranked tab, filtered by category). Start with Metroidvanias.
- The `/staff/reviews/steam-links` Unranked tab now supports category + year filters to focus checking efforts.
- The `review_score` field from Steam API uses a 0–9 scale (9 = Overwhelmingly Positive, 8 = Very Positive, 7 = Mostly Positive).
- Target ~10 games in queue per category before generating a post.
- Deduplication by `steam_id`: the same game may exist as both an S1 and S2 record with the same Steam ID. The enqueue step should only queue one entry per `steam_id` per category. The draft generator should likewise deduplicate, and note both consoles where applicable.
- Stage 2: if a category has fewer than 10 eligible games, consider pulling from the parent category + all subcategories combined to reach the threshold.
- Related: #90 (Steam link/review infrastructure).
