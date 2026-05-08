# #132 — Steam-backed News Content (Editorial Auto-generation)

## Overview

Surface unranked Switch games that have positive Steam reviews as auto-generated editorial content under `/news`. Games with 0–2 Switch Scores reviews currently have no `rating_avg` signal, but Steam review data provides a usable quality signal. A new feature queue bucket selects and prioritises these games; the existing auto-generation machinery produces a draft news post on cadence.

The editorial angle: *"These Switch games haven't been reviewed much here yet, but Steam players love them."*

---

## Background

### Existing system (already built)
- `FeatureQueueBucket` enum — `HAS_2_REVIEWS`, `HAS_1_REVIEW`, `HAS_0_REVIEWS`, `NEWLY_RANKED`, `FORGOTTEN_GEM`
- `feature_queue` table — priority scoring, cooldown logic
- `features:enqueue` artisan command — filters games, calculates priority, inserts into queue
- `generateBucketDraft()` in `DashboardController` — auto-generates a draft news post from a queue
- `NewsPostTypes` table — cadence management (publish every N days)
- `news_post_games` junction table — links news posts back to featured games
- Steam data: `steam_review_data` table (`review_score`, `total_positive`, `total_negative`, `total_reviews`), `steam_status` enum on `games` table

### What's missing
The existing buckets score games by `rating_avg`, which is null for 0-review games. A Steam bucket needs a different priority signal drawn from `steam_review_data`.

---

## Proposed Implementation

### 1. New enum case

Add to `App\Enums\FeatureQueueBucket`:

```php
UNRANKED_STEAM_GEM = 'unranked-steam-gem'
```

### 2. New NewsPostTypes entry

Seeder or migration to add:

```
slug: 'unranked-steam-gem'
title: 'Unranked Switch games loved on Steam'
cadence_days: 21
```

### 3. Extend `features:enqueue` command

Handle the new bucket with:
- Filter: `review_count < 3`, `steam_status = linked`, join `steam_review_data`
- Minimum quality threshold: `review_score >= 8` (Very Positive or above) OR configurable via `--min-steam-score`
- Priority calculation: `(review_score * 10) + log(total_reviews)` or similar — weights sentiment + volume
- Cooldown: same mechanism as existing buckets (`--cooldown-days`)

### 4. Staff dashboard

Add the new bucket to `/staff/news/dashboard`:
- "Generate draft: Unranked games loved on Steam" link (alongside existing "Has 2 reviews" link)
- Bucket management link to view ready/used queue

### 5. Generated content

The draft news post should include:
- Title: e.g. "Unranked Switch games loved on Steam — [Month Year]"
- URL: `/news/[YYYY-MM-DD]/unranked-switch-games-loved-on-steam-[date]`
- Intro copy explaining the Steam signal context
- Steam sentiment shown per game (e.g. "Very Positive — 1,200 reviews") alongside the standard `[gameblurb]` shortcode
- Category: featured games (ID 8)

---

## Decisions Log

| # | Decision |
|---|----------|
| 1 | Use existing `generateBucketDraft()` pattern rather than a new generation path. Extend the method or add a Steam-aware variant. |
| 2 | Minimum Steam score threshold: `review_score >= 8` (Very Positive). May need a flag to include score 7 (Mostly Positive) if coverage is thin. |
| 3 | Scope: Switch 1 and Switch 2 games both eligible. May want console-split posts later but start combined. |

---

## Notes

- Steam coverage is currently sparse — the list may be thin until more games are linked. Consider a lower threshold (`review_score >= 7`) to start.
- The `review_score` field from Steam API uses a 0–9 scale (9 = Overwhelmingly Positive, 8 = Very Positive, 7 = Mostly Positive).
- Related: #90 (Steam link/review infrastructure).
