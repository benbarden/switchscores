# Task #3: Unified Browse System

**Status:** Planning
**Date:** 2026-03-22
**Priority:** High (SEO impact - proceed carefully)

## Summary

Redesign the browse system to treat console, category, series, collection, tag, and year as combinable filters rather than separate page hierarchies. This creates a more flexible discovery experience while maintaining SEO-optimized URLs for important combinations.

## Current State

```
/c/switch-1/                        → Switch 1 landing
/c/switch-1/category/adventure      → Switch 1 Adventure games
/c/switch-1/series/mario            → Switch 1 Mario games
/c/switch-1/collection/lego         → Switch 1 LEGO games
/c/switch-1/tag/pixel-art           → Switch 1 pixel-art games
/c/switch-2/...                     → Same structure for Switch 2
```

**Problems:**
- Sparse/empty Switch 2 pages (bad for SEO)
- No cross-console browsing
- Rigid structure - can't combine filters (e.g., Adventure + pixel-art)

## Proposed Architecture

### Core Concept

All game attributes are filters:
- **Console:** Switch 1, Switch 2, All
- **Category:** Adventure, Puzzle, RPG, etc.
- **Series:** Mario, Zelda, Pokemon, etc.
- **Collection:** LEGO, Cozy games, etc.
- **Tag:** pixel-art, roguelike, multiplayer, etc.
- **Year:** 2017-2026
- **Sort:** Rank, Recent, Title

### URL Structure

**SEO-optimized paths for important combinations:**
```
/browse/                                    → All games (landing page)
/browse/switch-1/                           → All Switch 1 games
/browse/switch-2/                           → All Switch 2 games
/browse/category/adventure/                 → Adventure games (all consoles)
/browse/switch-1/category/adventure/        → Switch 1 Adventure games
/browse/switch-2/category/adventure/        → Switch 2 Adventure games
/browse/series/mario/                       → Mario games (all consoles)
/browse/switch-1/series/mario/              → Switch 1 Mario games
/browse/collection/lego/                    → LEGO games (all consoles)
/browse/tag/pixel-art/                      → Pixel-art games (all consoles)
```

**Additional filters via query params:**
```
/browse/switch-1/category/adventure/?tag=pixel-art&sort=recent
/browse/category/adventure/?year=2024
```

### URL Pattern Summary

| Pattern | Example | Page Title |
|---------|---------|------------|
| `/browse/` | - | Browse all Nintendo Switch games |
| `/browse/{console}/` | `/browse/switch-1/` | Nintendo Switch 1 games |
| `/browse/category/{cat}/` | `/browse/category/adventure/` | Adventure games |
| `/browse/{console}/category/{cat}/` | `/browse/switch-1/category/adventure/` | Nintendo Switch 1 Adventure games |
| `/browse/series/{series}/` | `/browse/series/mario/` | Mario series games |
| `/browse/{console}/series/{series}/` | `/browse/switch-1/series/mario/` | Nintendo Switch 1 Mario games |
| `/browse/collection/{col}/` | `/browse/collection/lego/` | LEGO collection games |
| `/browse/tag/{tag}/` | `/browse/tag/pixel-art/` | Pixel-art games |

### Filter Combinations

**Valid combinations:**
- Console + Category ✓
- Console + Series ✓
- Console + Collection ✓
- Console + Tag ✓
- Category + Tag ✓ (via query param)
- Any + Year ✓ (via query param)
- Any + Sort ✓ (via query param)

**Invalid/unlikely combinations:**
- Category + Series (games have one of each, not both)
- Series + Collection (same reason)

### Sort Options

- **Rank** (default) - by game_rank, highest first
- **Recent** - by release date, newest first
- **Title** - alphabetical

---

## Redirect Strategy

### Old → New URL Mapping

| Old URL | New URL | Status |
|---------|---------|--------|
| `/c/switch-1/` | `/browse/switch-1/` | 301 |
| `/c/switch-1/category` | `/browse/switch-1/category/` | 301 |
| `/c/switch-1/category/adventure` | `/browse/switch-1/category/adventure/` | 301 |
| `/c/switch-1/series` | `/browse/switch-1/series/` | 301 |
| `/c/switch-1/series/mario` | `/browse/switch-1/series/mario/` | 301 |
| `/c/switch-1/collection` | `/browse/switch-1/collection/` | 301 |
| `/c/switch-1/collection/lego` | `/browse/switch-1/collection/lego/` | 301 |
| `/c/switch-1/tag` | `/browse/switch-1/tag/` | 301 |
| `/c/switch-1/tag/pixel-art` | `/browse/switch-1/tag/pixel-art/` | 301 |
| Same patterns for switch-2... | | |

**Pages that stay as-is (not part of browse):**
- `/c/switch-1/top-rated` → Keep (console-specific rankings)
- `/c/switch-1/new-releases` → Keep (console-specific)
- `/c/switch-1/upcoming` → Keep (console-specific)
- `/c/switch-1/2024` (by year) → Keep (console-specific)

---

## Implementation Phases

### Phase 0: Preparation
- [ ] Document current SEO rankings for key pages (baseline)
- [ ] Set up monitoring (Search Console, rankings tracker)
- [ ] Finalise URL structure decisions

### Phase 1: Infrastructure
- [ ] Update repositories (already done - optional consoleId)
- [ ] Create unified BrowseController with filter logic
- [ ] Create route structure with optional console segment
- [ ] Create V2 templates with console badges

### Phase 2: Category Rollout (Test)
- [ ] Launch `/browse/category/` and `/browse/{console}/category/` routes
- [ ] Add 301 redirects from `/c/{console}/category/` to new URLs
- [ ] Update internal links
- [ ] Update sitemap
- [ ] Monitor SEO impact for 2-4 weeks

### Phase 3: Full Rollout (If Phase 2 OK)
- [ ] Roll out series, collection, tag
- [ ] Add combined filter support (category + tag)
- [ ] Full redirect mapping
- [ ] Remove old routes (after redirect period)

### Phase 4: Navigation & Polish
- [ ] Update main navigation
- [ ] Add filter UI to browse pages
- [ ] Landing page improvements

---

## SEO Monitoring Checklist

**Before launch:**
- [ ] Export current rankings for top 50 category/series/collection/tag pages
- [ ] Note current indexed page count in Search Console
- [ ] Screenshot Search Console performance (clicks, impressions)

**After each phase:**
- [ ] Check 301 redirects are working (test sample URLs)
- [ ] Verify new pages are being indexed
- [ ] Monitor Search Console for crawl errors
- [ ] Track ranking changes for key pages
- [ ] Watch for traffic drops

**Red flags to watch for:**
- Significant ranking drops (>10 positions) on key pages
- Crawl errors in Search Console
- Drop in indexed pages
- Traffic decline >20%

---

## Technical Notes

### Repository Changes (Already Done)

The repository methods were updated to support optional `consoleId`:
- `rankedByCategory($categoryId, $consoleId = null, $limit = null)`
- `rankedBySeries($seriesId, $consoleId = null, $limit = null)`
- `rankedByCollection($collectionId, $consoleId = null, $limit = null)`
- `rankedByTag($tagId, $consoleId = null, $limit = null)`
- Same pattern for unranked, delisted, lowQuality, hiddenGems, etc.

These changes are backwards-compatible and should be kept.

### Console Badge Support (Already Done)

The `game-card-modern.twig` template was updated to support `withConsole` parameter for showing S1/S2 badges.

---

## Open Questions

1. **Landing page at `/browse/`** - What should this show?
   - Links to category/series/collection/tag?
   - Featured games across all?
   - Stats/overview?

2. **Combined filters UI** - How to present filter options on the page?
   - Sidebar filters?
   - Top filter bar?
   - Pills/tags?

3. **Empty results** - What if a filter combination has no games?
   - Show empty state with suggestions?
   - Don't allow the combination?

4. **Pagination** - For large result sets
   - Use existing list page pattern?
   - Infinite scroll?

---

## Related Items

- Original #3 (series pages) - superseded by this
- #4 (collection pages) - merged into this
- #26, #27, #71 - merged into this
- #11 + #30 (S2 URL handling) - related but separate

---

## Decision Log

| Date | Decision | Rationale |
|------|----------|-----------|
| 2026-03-22 | Pause initial /browse/ implementation | SEO concerns - need more careful planning |
| 2026-03-22 | Adopt Metacritic-style URL structure | Allows both merged and console-specific pages with SEO value |
| 2026-03-22 | Phase rollout starting with categories | Test SEO impact before full rollout |
