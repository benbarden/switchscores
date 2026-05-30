# Task #95 / #107 — Multiplayer Data Scraping

**Status:** Done
**Completed:** 2026-02-27

## Overview

Extended the game crawl system to scrape player and multiplayer data from Nintendo UK store pages. This allows games with only override URLs (no API link) to have their player data populated automatically.

## Problem

- ~50% of games have no API link (`eshop_europe_fs_id`), only a `nintendo_store_url_override`
- These games couldn't get player data from the API
- The `games.players` field was often NULL for these games
- Nintendo pages contain rich player/multiplayer info that wasn't being captured

## Solution

### 1. New Database Table: `game_scraped_data`

Stores raw scraped data per game (one row per game):

```sql
CREATE TABLE game_scraped_data (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    game_id INT UNSIGNED UNIQUE,
    players_local VARCHAR(20),      -- e.g., "1", "2-4"
    players_wireless VARCHAR(20),   -- e.g., "1-8"
    players_online VARCHAR(20),     -- e.g., "1-8"
    multiplayer_mode VARCHAR(50),   -- e.g., "Simultaneous"
    features_json JSON,             -- Array of feature names
    scraped_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE
);
```

### 2. New Game Fields

Added to `games` table:

| Field | Type | Description |
|-------|------|-------------|
| `multiplayer_mode` | varchar(50) | e.g., "Simultaneous" |
| `has_online_play` | boolean | Derived from features or online players |
| `has_local_multiplayer` | boolean | Derived from features or local players > 1 |
| `play_mode_tv` | boolean | TV mode supported |
| `play_mode_tabletop` | boolean | Tabletop mode supported |
| `play_mode_handheld` | boolean | Handheld mode supported |

### 3. HTML Structure Parsed

Nintendo UK pages have consistent HTML structure:

```html
<p class="game_info_title">Players</p>
<p class="game_info_text">
    Single System (1), Local Wireless (1-8), Online (1-8)
</p>

<p class="game_info_title">Multiplayer mode</p>
<p class="game_info_text">Simultaneous</p>

<p class="game_info_title">Features</p>
<p class="game_info_text features">
    <a title="TV mode">TV mode</a>,
    <a title="Local multiplayer">Local multiplayer</a>...
</p>
```

### 4. Scraper Class

`App\Domain\Scraper\NintendoCoUkGameData`:

- Takes HTML in constructor, parses immediately
- `getPlayersLocal()`, `getPlayersWireless()`, `getPlayersOnline()` — Individual player ranges
- `getCombinedPlayers()` — Combines all ranges into "1-8" format for `games.players`
- `getMultiplayerMode()` — Returns mode string
- `getFeatures()` — Returns array of feature names
- `hasOnlinePlay()`, `hasLocalMultiplayer()` — Derived booleans
- `hasPlayModeTv()`, `hasPlayModeTabletop()`, `hasPlayModeHandheld()` — Feature checks

### 5. Command Updates

**`game:crawl {gameId}`:**
- After successful 200 response, scrapes HTML
- Saves to `game_scraped_data`
- Updates game fields if values changed
- Shows detailed output of what was found/changed

**`game:crawl-batch`:**
- Same scraping logic
- Inline warnings for missing data: `[players still NULL]`
- Prioritises games with null `players` field

### 6. Crawl Priority Order

```php
$query->orderByRaw('nintendo_store_url_override IS NOT NULL DESC')
      ->orderByRaw('players IS NULL DESC')
      ->orderByRaw('last_crawled_at IS NULL DESC')
      ->orderBy('last_crawled_at', 'asc')
      ->orderBy('id', 'asc');
```

## Files Created

- `database/migrations/2026_02_27_125952_create_game_scraped_data_table.php`
- `database/migrations/2026_02_27_130843_add_multiplayer_fields_to_games.php`
- `app/Models/GameScrapedData.php`
- `app/Domain/Scraper/NintendoCoUkGameData.php`
- `tests/Unit/Domain/Scraper/NintendoCoUkGameDataTest.php`

## Files Modified

- `app/Console/Commands/Game/GameCrawlUrl.php` — Added scraping
- `app/Console/Commands/Game/GameCrawlBatch.php` — Added scraping + warnings + priority
- `app/Models/Game.php` — Added new fields to fillable
- `app/Http/Controllers/Staff/Games/DashboardController.php` — Added MissingPlayersCount
- `resources/views/staff/games/detail/detail-col1.twig` — Added "Players and modes" section
- `resources/views/staff/games/detail/detail-col2.twig` — Moved "Games companies" here
- `resources/views/staff/games/dashboard.twig` — Renamed to "Missing data", added row
- `resources/views/public/games/page/game-details.twig` — Added multiplayer/play mode display

## Testing

Run unit tests:
```bash
make test-filter F=NintendoCoUkGameDataTest
```

Test single game crawl:
```bash
php artisan game:crawl 152
```

Test batch crawl:
```bash
php artisan game:crawl-batch --limit=10
```

## Debugging Missing Data

Find games crawled but still missing players:
```sql
SELECT id, title FROM games
WHERE last_crawled_at IS NOT NULL
AND players IS NULL
ORDER BY last_crawled_at DESC
LIMIT 10;
```

Check if scraped data exists:
```sql
SELECT * FROM game_scraped_data WHERE game_id = 123;
```

## Future Considerations

- Could scrape more fields (publisher, developer from US pages)
- Could add more warnings to batch crawl for other missing fields
- Could create a staff list page for "games with no player data after crawl"
