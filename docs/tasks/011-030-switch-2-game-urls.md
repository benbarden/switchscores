# Task: Switch 2 Game URL Changes (#11 + #30)

## Goal

Change Switch 2 game URLs to use a new format that allows the same `link_title` on both consoles.

| Console | URL Format |
|---------|------------|
| Switch 1 | `/games/{id}/{link_title}` (unchanged) |
| Switch 2 | `/switch-2/games/{id}/{link_title}` (new) |

## Out of Scope (Future Work)

These items are intentionally deferred:

### 1. Migrate Switch 1 games to `/switch-1/games/{id}/{link_title}`

- Would provide URL consistency across consoles
- Requires redirecting thousands of established URLs
- High SEO risk - revisit once S2 URL pattern is proven

### 2. Drop `/c/` prefix from console pages

- Currently: `/c/switch-1/category/adventure`
- Future: `/switch-1/category/adventure`
- Would match the new game URL pattern
- Revisit after S2 game URLs are bedded in

---

## Implementation Plan

### Phase 1: Database Schema

**Migration: Add `console_id` to `game_title_hashes`**

File: `database/migrations/YYYY_MM_DD_add_console_id_to_game_title_hashes.php`

- Add `console_id` column (FK to consoles)
- Populate from games table via join
- Add composite unique index on (`title_hash`, `console_id`)

### Phase 2: Title Hash Logic

**Files to update:**

| File | Changes |
|------|---------|
| `app/Models/GameTitleHash.php` | Add `console_id` to `$fillable`, add console relationship |
| `app/Domain/GameTitleHash/Repository.php` | Add `consoleId` param to `create()`, new `titleHashExistsForConsole()` method |
| `app/Http/Controllers/Staff/Games/GamesEditorController.php` | Use console-scoped hash check on add (L107-117) and edit (L206-223) |
| `app/Domain/GameImport/JsonImportService.php` | Use console-scoped duplicate check (L82-86), pass console_id on create (L202) |

### Phase 3: Routes & Controller

**New route in `routes/public.php`:**

Add before existing game routes (~L91):

```php
// Switch 2 game pages (new URL format)
Route::middleware('throttle:60,1')->controller('PublicSite\Games\GameShowController')->group(function () {
    Route::get('/switch-2/games/{id}', 'showIdSwitch2')->name('game.showId.switch2');
    Route::get('/switch-2/games/{id}/{linkTitle}', 'showSwitch2')->name('game.show.switch2');
});
```

**Update `GameShowController.php`:**

- Add `showSwitch2()` and `showIdSwitch2()` methods (or refactor to shared method with console context)
- In `show()`: redirect S2 games from `/games/...` to `/switch-2/games/...` (301)
- Update canonical URL generation

**Update `RandomController.php`:**

- L26: Use console-aware URL generation instead of hardcoded sprintf

### Phase 4: URL Helper

**Update `app/Providers/TwigViewServiceProvider.php`:**

Update `game_url()` Twig function (L19-24):

```php
$twig->addFunction(new TwigFunction('game_url', function ($game) {
    if ($game->console_id === \App\Models\Console::SWITCH_2) {
        return route('game.show.switch2', [
            'id'        => $game->id,
            'linkTitle' => $game->link_title,
        ]);
    }
    return route('game.show', [
        'id'        => $game->id,
        'linkTitle' => $game->link_title,
    ]);
}));
```

**Consider creating `app/Support/GameUrl.php`:**

Reusable helper class for controllers to avoid duplication.

### Phase 5: Template Updates

Templates that call `route('game.show', ...)` directly must be updated to use `game_url()` instead.

**Critical templates (~35 files):**

- `resources/views/sitemap/games.twig`
- `resources/views/ui/components/links/game.twig`
- `resources/views/ui/components/game/packshots.twig`
- `resources/views/ui/components/game/game-card-modern.twig`
- `resources/views/ui/components/game/game-card-standard.twig`
- `resources/views/ui/components/game/card-v2.twig`
- `resources/views/ui/blocks/shortcodes/game-table.twig`
- `resources/views/ui/blocks/shortcodes/game-grid.twig`
- `resources/views/public/games/page/show.twig` (L206)
- All `tileXxx.twig` templates in `resources/views/public/games/layouts/tiledGrid/`

### Phase 6: API Updates

**Files:**

- `app/Http/Controllers/Api/Game/GameController.php` (L51)
- `app/Http/Controllers/Api/V2/GameController.php` (L71)

Update `parseGameData()` methods to use console-aware URL generation.

### Phase 7: 301 Redirects

**In `GameShowController::show()`:**

1. If game is Switch 2 AND accessed via `/games/...` → 301 redirect to `/switch-2/games/...`
2. For S2 games with "(Switch 2)" suffix in old link_title → redirect to clean URL

### Phase 8: Data Migration

**Artisan command to clean up existing S2 link titles:**

1. Find S2 games with "(Switch 2)" in link_title
2. Generate clean link_title (remove suffix)
3. Check for conflicts (should now be allowed)
4. Update link_title
5. Store old link_title for redirect mapping (optional: in a redirects table or config)

### Phase 9: Testing

**Update existing tests:**

- `tests/Page/GamesTest.php` - add S2 URL tests

**New tests:**

- S2 game URLs work correctly
- Legacy URL redirects (301)
- Title hash uniqueness is console-scoped
- API returns correct URLs per console

---

## Key Files Reference

| Purpose | File |
|---------|------|
| Route definitions | `routes/public.php` |
| Twig URL helper | `app/Providers/TwigViewServiceProvider.php` |
| Game page controller | `app/Http/Controllers/PublicSite/Games/GameShowController.php` |
| Title hash repo | `app/Domain/GameTitleHash/Repository.php` |
| Title hash model | `app/Models/GameTitleHash.php` |
| Game editor | `app/Http/Controllers/Staff/Games/GamesEditorController.php` |
| JSON import | `app/Domain/GameImport/JsonImportService.php` |
| Sitemap | `resources/views/sitemap/games.twig` |
| Game link component | `resources/views/ui/components/links/game.twig` |
| Packshots component | `resources/views/ui/components/game/packshots.twig` |

---

## Execution Order

1. Phase 1: Database migration (can be run independently)
2. Phase 2: Title hash logic (enables same title on both consoles)
3. Phase 3-4: Routes + URL helper (core URL change)
4. Phase 5-6: Templates + API (propagate URL change)
5. Phase 7: Redirects (SEO preservation)
6. Phase 8: Data cleanup (remove "(Switch 2)" suffixes)
7. Phase 9: Testing

---

## Implementation Status (2026-04-03)

| Phase | Status | Notes |
|-------|--------|-------|
| 1. Database migration | DONE | `2026_04_03_000001_add_console_id_to_game_title_hashes.php` |
| 2. Title hash logic | DONE | Repository + model + editor + import updated |
| 3. Routes + Controller | DONE | New routes + `showSwitch2()` + redirects in `show()` |
| 4. URL helper | DONE | `game_url()` Twig function updated |
| 5. Template updates | DONE | ~30 templates updated to use `game_url()` |
| 6. API updates | DONE | Both API controllers updated |
| 7. Redirects | DONE | Handled in controller (S2 via /games/ → 301 to /switch-2/games/) |
| 8. Data cleanup | DONE | `game:cleanup-switch2-link-titles` command created |
| 9. Testing | TODO | Manual testing needed |

### Files Changed

**New files:**
- `database/migrations/2026_04_03_000001_add_console_id_to_game_title_hashes.php`
- `app/Console/Commands/Game/GameCleanupSwitch2LinkTitles.php`

**Modified files:**
- `app/Models/GameTitleHash.php`
- `app/Domain/GameTitleHash/Repository.php`
- `app/Http/Controllers/Staff/Games/GamesEditorController.php`
- `app/Domain/GameImport/JsonImportService.php`
- `routes/public.php`
- `app/Http/Controllers/PublicSite/Games/GameShowController.php`
- `app/Http/Controllers/PublicSite/Games/RandomController.php`
- `app/Providers/TwigViewServiceProvider.php`
- `app/Http/Controllers/Api/Game/GameController.php`
- `app/Http/Controllers/Api/V2/GameController.php`
- ~30 Twig templates (updated to use `game_url()`)

### Remaining Template

One staff template still uses direct `route()` call (works via redirect):
- `resources/views/staff/games-companies/list-unranked.twig` - uses join query fields

### To Test

1. Run migration: `php artisan migrate`
2. Test S2 game URL: `/switch-2/games/{id}/{slug}`
3. Test S1 game URL: `/games/{id}/{slug}` (unchanged)
4. Test redirect: S2 game accessed via `/games/...` → 301 to `/switch-2/games/...`
5. Test adding game with same title on both consoles
6. Run cleanup command: `php artisan game:cleanup-switch2-link-titles --dry-run`
