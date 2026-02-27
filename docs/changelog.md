# Changelog

Development history and completed work.

---

## 2026-02-27 — Multiplayer Data Scraping (Tasks #95, #107)

**Summary:**
Extended the crawl system to scrape player and multiplayer data from Nintendo UK pages. Games with override URLs (no API link) can now get player data populated automatically.

**New Database Table:**
- `game_scraped_data` — Stores scraped data per game:
  - `players_local` (e.g., "1", "2-4")
  - `players_wireless` (e.g., "1-8")
  - `players_online` (e.g., "1-8")
  - `multiplayer_mode` (e.g., "Simultaneous")
  - `features_json` (array of features)
  - `scraped_at` timestamp

**New Game Fields:**
- `multiplayer_mode` — varchar(50)
- `has_online_play` — boolean
- `has_local_multiplayer` — boolean
- `play_mode_tv` — boolean
- `play_mode_tabletop` — boolean
- `play_mode_handheld` — boolean

**New Scraper:**
- `App\Domain\Scraper\NintendoCoUkGameData` — Parses Nintendo UK HTML pages
- Extracts: Players (local/wireless/online), Multiplayer mode, Features
- `getCombinedPlayers()` — Combines all player ranges into "1-8" format for `games.players`

**Updated Commands:**
- `game:crawl` — Now scrapes game data on successful 200 response
- `game:crawl-batch` — Same scraping, plus inline warnings for missing data (e.g., `[players still NULL]`)
- Batch crawl prioritises games with null `players` field

**Staff UI:**
- Games detail: New "Players and modes" section showing all multiplayer fields
- Dashboard: Renamed "Watchlists" to "Missing data", added "Missing players" counter

**Public UI:**
- Game page: Shows multiplayer mode, online/local multiplayer badges, play mode badges (TV/Tabletop/Handheld)

**Unit Tests:**
- `tests/Unit/Domain/Scraper/NintendoCoUkGameDataTest.php` — 25+ test cases covering:
  - `getCombinedPlayers()` with various player configurations
  - Individual field parsing
  - Boolean feature flag detection
  - Edge cases (empty HTML, whitespace)

**Related improvements.md tasks closed:**
- #95 — GH76: multiplayer options
- #107 — Store Local vs Online player counts separately

---

## 2026-02-23 — Game Crawl System (Task #110)

**Summary:**
Built infrastructure to crawl Nintendo URLs and detect broken links across ~8430 games with manual override URLs. Sustainable approach: 100 games/night via cron, full cycle in ~3 months.

**Commands:**
- `php artisan game:crawl {gameId} [--save-html]` — Single game crawl with optional HTML debug output
- `php artisan game:crawl-batch [--limit=100] [--delay=3] [--source=override]` — Batch crawl with rate limiting

**Database:**
- `games.last_crawled_at` — Timestamp of last crawl
- `games.last_crawl_status` — HTTP status code (200, 404, 410, etc.)
- `game_crawl_lifecycle` table — Logs problems (non-200) and recoveries (200 after problem) only

**Staff UI:**
- Dashboard: Crawl lifecycle stats (Not yet crawled, 200 OK, 404, 410, redirects, errors)
- Clickable links to list pages for each problem status
- GameDetail: New "Crawl lifecycle" tab showing current status and history

**Cron:**
```
30 6 * * * php artisan game:crawl-batch --limit=100 --delay=3
```

**Key design decisions:**
- Only active games are crawled (delisted/soft-deleted excluded)
- Lifecycle table only stores state changes (not routine 200s) to avoid bloat
- Must clear game cache after crawl (`clearCacheCoreData()`) due to ONE_DAY caching

**Also completed:**
- Created `docs/coding-standards.md` documenting repository pattern and other standards
- Created `App\Domain\Game\Repository\GameCrawlRepository` for crawl status queries

**Related tasks for future phases:**
- #70 — Re-download hi-res images (can use crawl infrastructure)
- #10 — Scrape publisher, players from Nintendo URL
- #95/#107 — Scrape multiplayer options (Local/Online player counts)

---

## Planned: GameCreationService Refactor

**Goal:** Consolidate game creation logic into a single service class.

**Current state after removing bulk-add and CSV import:**
- `GamesEditorController::add()` - Full form, single game
- `ReleaseHubController::store()` - Quick AJAX add
- `JsonImportService` - Bulk JSON import

**Proposed service:**
```php
class GameCreationService
{
    public function create(GameCreationParams $params): Game
    {
        // 1. Check title hash uniqueness (throw if exists)
        // 2. Generate link_title
        // 3. Create game via GameDirectorFactory
        // 4. Set optional fields (batch_date, category_verification, eu_released_on)
        // 5. Create title hash
        // 6. Link publisher + quality filter (if provided)
        // 7. Download images (if URLs provided)
        // 8. Fire GameCreated event
        // 9. Return game
    }
}
```

**Migration order:**
1. Write tests for the new service
2. Migrate `JsonImportService` (already closest to pattern)
3. Migrate `ReleaseHubController::store()`
4. Migrate `GamesEditorController::add()`
5. Remove unused builder/director code

---

## 2026-01-30 — Staff JSON Game Import Tool

**Summary:**
Added a new staff tool to import games from a JSON file, designed for the weekly workflow of adding new/upcoming games extracted from Nintendo's website.

**Features:**
- Upload JSON file with batch of games
- Validation with preview before import:
  - Duplicate detection (by title hash)
  - Category validation (must exist in DB)
  - Console validation (switch-1 or switch-2)
  - Publisher lookup with auto-creation for new publishers
- Shows validation errors and new publishers to be created
- Imports games with all mapped fields (title, release date, price, players, category, etc.)
- Downloads packshot images:
  - Square image from direct URL
  - Header image scraped from Nintendo store page
- Sets `category_verification = 1` for games imported with a category

**Architecture improvements:**
- Created dedicated `Domain/GameImport/` module with clean separation:
  - `JsonImportService` - Main orchestration
  - `ImportGameData` - Value object for parsed game data
  - `ImportResult` - Validation results container
  - `SquareImageDownloader` - Downloads square image from direct URL
  - `HeaderImageScraper` - Scrapes store page for header image
- Image downloaders are separate classes (not conflated like the older `DownloadPackshotHelper`), each handling one source type

**Routes:**
- `GET /staff/games/json-import` - Upload form
- `POST /staff/games/json-import/preview` - Validate and preview
- `POST /staff/games/json-import/confirm` - Execute import

**Removed (superseded by JSON import):**
- `BulkEditorController::bulkAdd()` - 20-row form, no publisher support
- `BulkEditorController::importFromCsv()` - TSV paste, fewer fields
- Associated routes, templates, and nav links

---

## 2026-01-26 — Staff Section Bootstrap 5 Migration

**Summary:**
Migrated all staff section templates from Bootstrap 3 to Bootstrap 5.

**Sections migrated:**
- News (8 files)
- Stats (3 files)
- Partners (8 files)
- Games Companies (9 files)
- Reviews (23 files)
- Games (40+ files)

**Key changes:**
- Layout extends changed from `theme/wos/staff/` to `theme/staff-b5/`
- Table sorting includes changed from `table-sorting-b3.twig` to `table-sorting-b5.twig`
- Form classes updated (`form-group` → `row mb-3`, `control-label` → `col-form-label`, etc.)
- Labels updated (`label label-*` → `badge bg-*`)
- Buttons updated (`btn-xs` → `btn-sm`, `btn-default` → `btn-outline-secondary`)
- Select elements updated (`form-control` → `form-select`)

**Additional improvements:**
- Added visual section styling to game editor form (fieldset backgrounds/borders)
- Fixed Data checks component (`ui/components/checks/row.twig`)
- Added `renderB5Horizontal` macro to category dropdown component

**Files updated outside staff templates:**
- `ui/components/checks/row.twig`
- `ui/components/taxonomy/category-dropdown.twig`
- `ui/components/staff/game/bulk-edit-table.twig`

---

## 2025-08-11 — Staff Game Lists Refactor

**Summary:**  
All staff game list pages are now handled through a single `showList()` method, using a unified `listConfig()` array and optional `getDynamicTitle()` helper.  
Previously, each list type had its own route, controller method, and (often) duplicate logic.

**Benefits:**
- One route (`staff.games.list.showList`) handles all lists.
- Titles and breadcrumbs for special cases (e.g. category, series, tag, format-option) are generated dynamically in `getDynamicTitle()`.
- Adding a new list requires only:
    1. Adding a config entry to `listConfig()` (with `title`, `fetch` closure, and optional `dynamicTitle` flag).
    2. Adding a matching case in `getDynamicTitle()` (if dynamic).
    3. Adding a link in the UI with `listType` and optional `param1`, `param2`.
- Future-proof for Laravel route model binding if adopted later.
- Templates now consistently use:
```twig
  {{ route('staff.games.list.showList', { 'listType': 'by-category', 'param1': category }) }}
```

**Example — Adding a New List:**

1. Add to `listConfig()`:
   ```php
   'new-list' => [
       'title' => 'My New List',
       'fetch' => function () {
           return $this->repoGameLists->newListMethod();
       },
   ],
   ```

2. (Optional) Add to `getDynamicTitle()` if it needs a dynamic title:
   ```php
   case 'new-list':
       return 'My New List';
   ```

3. Link to it from the UI:
   ```twig
   {{ route('staff.games.list.showList', { 'listType': 'new-list' }) }}
   ```

---

**Completed in this refactor:**
- 21 list types converted.
- Obsolete routes and controller methods removed.
- Verified all staff list pages work via `showList()`.
