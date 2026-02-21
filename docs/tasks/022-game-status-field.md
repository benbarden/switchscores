# Task #22: Add Game Status Field

## Overview

Add a dedicated `game_status` field to properly handle game lifecycle states (active, delisted, soft-deleted), separate from the API-driven `format_digital` field.

## Current State

### `format_digital` Field
- Nullable string field on `games` table
- Constants in `App\Models\Game`:
  - `FORMAT_AVAILABLE = 'Available'`
  - `FORMAT_DELISTED = 'De-listed'`
  - `FORMAT_NOT_AVAILABLE = 'Not available'`
- Helper: `isDigitalDelisted()` method

### Data Flow (UpdateAvailability Command)
1. `updateDigitalAvailable()`: Games with `eshop_europe_fs_id` in API results → `Available`
2. `updateDigitalDelisted()`: Games NOT in API results (and no override URL) → `De-listed`
3. `nintendo_store_url_override` can override back to `Available`

### Current Query Pattern
```php
->where('format_digital', '<>', Game::FORMAT_DELISTED)
->where('is_low_quality', 0)
```

### Files Using FORMAT_DELISTED (37+ files)
Key areas:
- `App\Domain\Category\Repository` - rankedByCategory, unrankedByCategory, hiddenGemsByCategory, delistedByCategory
- `App\Domain\Tag\Repository` - similar methods
- `App\Domain\GameSeries\Repository` - similar methods
- `App\Domain\GameCollection\Repository` - similar methods
- `App\Domain\GameRank\RankAllTime.php`, `RankYear.php` - raw SQL exclusions
- `App\Domain\GameStats\Repository` - totalActiveGames, totalRankedGames
- API controllers expose format_digital in responses

### De-listed Section Display
- V1 category templates show de-listed games in separate section at bottom
- V2 templates need verification - may exclude entirely

## Proposed Solution

### Phase 1: Create GameStatus Enum and Migration

**Create `App\Enums\GameStatus.php`:**
```php
<?php

namespace App\Enums;

class GameStatus
{
    const ACTIVE = 'active';
    const DELISTED = 'delisted';
    const SOFT_DELETED = 'soft_deleted';

    public static function all(): array
    {
        return [
            self::ACTIVE,
            self::DELISTED,
            self::SOFT_DELETED,
        ];
    }
}
```

**Migration:**
```php
// Add game_status column
Schema::table('games', function (Blueprint $table) {
    $table->string('game_status', 20)->default('active')->after('format_digital');
    $table->index('game_status');
});

// Populate from existing data
DB::statement("UPDATE games SET game_status = 'delisted' WHERE format_digital = 'De-listed'");
```

### Phase 2: Update Critical Queries

Priority areas to update (use new field alongside existing checks initially):

1. **Category Repository** - `rankedByCategory`, `unrankedByCategory`, etc.
2. **Tag Repository** - similar methods
3. **Series/Collection Repositories**
4. **Ranking calculations**

**New query pattern:**
```php
->where('game_status', GameStatus::ACTIVE)
->where('is_low_quality', 0)
```

### Phase 3: Update DataSource Flow

Modify `UpdateAvailability` command:
1. Continue updating `format_digital` from API (maintains history)
2. Update `game_status` with safeguards:
   - Only `active` → `delisted` if API confirms missing
   - **Never touch `soft_deleted`** - that's a manual override
   - Log conflicts for review

**Conflict Detection:**
When API says "available" but status is "delisted" (manual override), or vice versa:
- Log to a conflicts table or file
- Surface in staff dashboard for review

### Phase 4: Soft Delete Implementation

- Staff UI to soft-delete games
- Soft-deleted games: hidden from ALL public pages
- Return 410 status for soft-deleted game URLs (relates to #31)
- Different from delisted (which may still show in delisted sections)

## Safety Considerations

1. **Manual override wins** - if staff sets status, API shouldn't auto-override
2. **Audit trail** - consider `game_status_updated_at` or logging
3. **Gradual rollout** - update queries area by area, test each
4. **Conflict surfacing** - don't silently ignore API vs manual conflicts

## Implementation Progress (2026-02-21)

### DONE
- [x] `App\Enums\GameStatus` enum (ACTIVE, DELISTED, SOFT_DELETED)
- [x] Migration `2026_02_21_000001_add_game_status_to_games_table.php`
- [x] Game model: `$fillable`, cast, scopes (`active()`, `delisted()`), helpers (`isActive()`, `isDelisted()`, `isSoftDeleted()`)
- [x] `NintendoCoUk\UpdateGame.php` - updates game_status with safeguards:
  - Never touches SOFT_DELETED
  - Min threshold of 1000 link IDs (prevents mass de-listing on API failure)
- [x] `UpdateAvailability` command logs warnings if threshold not met
- [x] `Category\Repository` - updated to use `active()`/`delisted()` scopes
- [x] Staff GameDetail page - inline status editor (badge + Edit → dropdown + Save/Cancel)
- [x] Staff game editor - game_status dropdown at top (edit mode only)
- [x] `GameBuilder` and `GameDirector` - handle game_status field
- [x] `GamesDetailController::updateStatus()` - AJAX endpoint for quick status change
- [x] Public game pages - 410 response for soft_deleted games
- [x] `resources/views/errors/410.twig` - error page for removed games

### TODO - Repositories to update
All repositories have been updated to use `game_status` field instead of `format_digital`:
- [x] `App\Domain\GameCalendar\Repository` - all methods updated
- [x] `App\Domain\GameCalendar\Stats` - all methods updated
- [x] `App\Domain\Tag\Repository` - similar methods to Category
- [x] `App\Domain\GameStats\Repository` - totalRanked, totalReleased
- [x] `App\Domain\GameStats\DbQueries` - siteStatsByConsole
- [x] `App\Domain\GameRank\RankAllTime.php` - raw SQL queries
- [x] `App\Domain\GameRank\RankYear.php` - raw SQL queries
- [x] `App\Domain\TopRated\DbQueries.php` - rankedCountByConsoleAndYear, rankedCountByYear
- [x] `App\Domain\GameLists\Repository` - recentlyReleasedByCategory, noNintendoCoUkLink
- [x] `App\Domain\GameLists\DbQueries` - getByTagWithDates
- [x] `App\Domain\GameSeries\Repository` - ranked/unranked/delisted/lowQuality methods
- [x] `App\Domain\GameCollection\Repository` - ranked/unranked/delisted/lowQuality methods
- [x] `App\Domain\Unranked\Repository` - all methods updated
- [x] `App\Domain\GameSearch\Builder` - hide de-listed check
- [x] `App\Domain\GameDeveloper\DbQueries` - ranked/unranked/delisted methods
- [x] `App\Domain\GamePublisher\DbQueries` - ranked/unranked/delisted methods
- [x] `App\Domain\Game\Repository` - randomGame, partialTitleSearch
- [x] `App\Domain\Game\Repository\GameStatsRepository` - totalReleased, totalRanked
- [x] `App\Domain\Game\Repository\CategoryVerificationRepository` - getOldestUnverifiedGames
- [x] `App\Domain\IntegrityCheck\Repository` - getGameMissingRank
- [x] `App\Http\Controllers\Staff\Games\GamesListController` - no-tag-category list
- [x] `App\Http\Controllers\Api\V2\User\CollectionController` - isDeListed check

**Note:** Sitemap automatically uses `GameCalendar\Repository::byYear()` which now uses `active()` scope,
so soft_deleted games are excluded from sitemaps.

## Testing Checklist

- [x] Migration runs without errors
- [x] Existing de-listed games have correct status
- [ ] Category pages show correct games (v1 and v2)
- [ ] Rankings exclude de-listed correctly
- [ ] API responses still work
- [x] UpdateAvailability command respects status field (tested - soft_deleted not touched)
- [x] Soft delete UI works
- [x] 410 status returned for soft-deleted games

## Related Tasks

- #31 - Hold deleted URLs; send 410 status to Google
- #111 - Refactor App\Domain folder structure (may affect where status logic lives)
