# #130 — Weekly Update Tool (Staff)

## Overview

Replace the current manual Claude Code session-based process for weekly game additions with a fully integrated staff web tool. The process currently involves copying raw Nintendo listing data into text files, processing them in a separate Claude Code project, and importing JSON. This task moves the entire pipeline into the Switch Scores staff section.

---

## Decisions Log

| # | Decision |
|---|----------|
| 1 | **Direct DB import** — no JSON step. But must show a clear "Confirm" step before anything is written. Confirmation is per-list (safer given Switch 1 lists can reach 40 items each). |
| 2 | **CategorySuggester first** — build rule-based suggestions. For games with no suggestion, offer a "copy prompt to Claude" button as a fast interim. Revisit AI API later if needed. |
| 3 | **Per-page raw input for Switch 1** — add/view/replace pages one at a time. Switch 2 is single-page. Same UI for both, Switch 2 just won't need more than page 1. |
| 4 | **Background job for fetching** — user explicitly kicks off. Per-page batching. Show status per item. Throttle requests (1–2s gap between each). Polling/refresh OK for now. |
| 5 | **LQ in DB** — `GamesCompany.is_low_quality` is already the source of truth. Use it directly. |
| 6 | **URL collection order** — preserve raw paste order throughout. One page of games at a time. Text field per game. Nintendo game page URLs first, then packshot URLs (non-LQ only). |
| 7 | **Category review** — Accept/Reject per game. Reject → grouped dropdown. "Accept all" button. No suggestion → flagged clearly, user must pick. Use past DB selections to inform suggestions. |
| 8 | **All four lists** — same format and pipeline. Testing will start with Switch 2. |
| 9 | **URL base path** — `/staff/games/weekly-updates`. Sub-paths: `/{id}`, `/{id}/{console}/{list}/raw`, `/urls`, `/fetch`, `/packshots`, `/categories`, `/confirm`. |
| 10 | **Auto-ordering at import** — write `sort_order` from batch item to `eshop_europe_order` on the game at import time. Release Hub then shows newly imported games in Nintendo-listing order by default. |
| 11 | **Missing games check** — shown at parse results stage. Query DB for games on that console in the date range not present in the raw data. Per game: re-check URL option (queued fetch). Results: 404, redirect loop, date changed, still exists. |
| 12 | **`item_status` enum** — see Item Status section below. No `confirmed` state needed; confirmation is a dialog click, not a DB state. |

---

## Current Process Summary

Each Friday, four lists are processed:
- Switch 2 New Releases
- Switch 2 Upcoming
- Switch 1 New Releases
- Switch 1 Upcoming

Each list goes through steps A–F:
1. **Parse** raw Nintendo listing text → extract title/date/price/genres, filter by date, deduplicate against DB, normalise titles/publishers, flag LQ and bundles
2. **Collect URLs** — user provides Nintendo game page URLs for each game (raw paste order)
3. **Fetch game data** — visit each URL for publisher, players, price, description
4. **LQ check** — confirm Low Quality status, move games to correct bucket
5. **Collect packshots** — user provides packshot URLs (not scrapable, JS-rendered)
6. **Review prices** — flag £0.00, sale prices, listing vs game page mismatches
7. **Confirm categories** — suggest then user confirms, one SS category per game
8. **Confirm + import** — review and write directly to DB

---

## Proposed Database Schema

### `weekly_batches`

| Column | Type | Notes |
|--------|------|-------|
| id | int PK | |
| batch_date | date | Friday date for the week |
| status | enum | `setup`, `in_progress`, `complete` |
| created_at | timestamp | |
| updated_at | timestamp | |

### `weekly_batch_items`

| Column | Type | Notes |
|--------|------|-------|
| id | int PK | |
| batch_id | int FK | → weekly_batches |
| console | enum | `switch-1`, `switch-2` |
| list_type | enum | `new`, `upcoming` |
| page_number | int | Which raw page this came from (1–5 for S1) |
| sort_order | int | Position within that page (raw paste order preserved) |
| title | varchar | Normalised title |
| title_raw | varchar | Original from paste |
| release_date | date | |
| price_gbp | decimal(8,2) | null if unknown |
| price_raw | varchar | Original string e.g. "£6.02£4.30*" |
| nintendo_url | varchar | Game page URL (user-provided) |
| packshot_url | varchar | 1x1 square image URL (user-provided) |
| publisher_raw | varchar | From Nintendo page fetch |
| publisher_normalised | varchar | After name mapping |
| players | varchar | e.g. "1", "1-4" |
| nintendo_genres | varchar | Comma-separated from listing |
| description | text | From game page fetch |
| suggested_category | varchar | Category name (auto-suggested) |
| category | varchar | Confirmed category name |
| collection | varchar | link_title e.g. "arcade-archives-2" |
| item_status | enum | See Item Status section. Groups: skipped / active / done. |
| lq_flag | tinyint | 1 = a flag triggered lq_review (survives user decision, records reason) |
| lq_publisher_name | varchar | Publisher name that triggered the flag |
| price_flag | tinyint | 1 = price needs review |
| price_flag_reason | varchar | e.g. "sale price detected", "£0.00" |
| fetch_status | enum | `pending`, `queued`, `fetching`, `fetched`, `failed` |
| fetch_error | varchar | HTTP error message if failed |
| game_id | int FK | → games (set after import) |
| notes | text | Manual notes |
| created_at | timestamp | |
| updated_at | timestamp | |

---

## Item Status

The `item_status` field tracks pipeline position and final disposition. Items fall into three groups:

### Groups

| Group | Statuses | Terminal? |
|-------|---------|-----------|
| **Skipped** | `already_in_db`, `out_of_range`, `low_quality`, `bundle` | Yes — not imported, kept in batch for record |
| **Active** | `pending`, `fetch_pending`, `lq_review`, `packshot_pending`, `category_pending`, `ready` | No — moving through pipeline |
| **Done** | `imported` | Yes — written to DB |

The model has helper methods: `isSkipped()`, `isActive()`, `isImported()`.

### Active pipeline (who acts at each stage)

| Status | Who acts | What they do |
|--------|----------|-------------|
| `pending` | User | Provides Nintendo game page URL |
| `fetch_pending` | System | Fetches URL via background job |
| `lq_review` | User | Decides: confirm `low_quality` / confirm `bundle` / keep (→ `packshot_pending`) |
| `packshot_pending` | User | Provides packshot URL |
| `category_pending` | User | Confirms category |
| `ready` | User | Reviews list and clicks "Confirm & Import" |
| `imported` | — | Done |

### Transitions

```
[parse]     → pending
[parse]     → already_in_db     (title + console already in games DB)
[parse]     → out_of_range      (date outside week filter)
[parse]     → bundle            (clear bundle pattern in title — user can override)

pending     → fetch_pending     (user provides URL)

fetch_pending → lq_review       (fetch done AND any flag set: publisher is_low_quality, unknown publisher, or LQ signals)
fetch_pending → packshot_pending (fetch done, no flags)

lq_review   → low_quality       (user confirms LQ)
lq_review   → bundle            (user confirms bundle)
lq_review   → packshot_pending  (user keeps item — lq_flag stays set for record)

packshot_pending → category_pending (user provides packshot URL)

category_pending → ready        (user confirms category)

ready       → imported          (user clicks Confirm & Import — dialog confirms, then immediate write)
```

### Notes
- `lq_flag` tinyint column stays alongside `item_status`. Set to 1 whenever a flag triggered `lq_review`. Survives the user decision so the reason is recorded even if item is kept.
- `lq_flag` is distinct from `item_status = low_quality`: the flag is "this was flagged", the status is "this was confirmed LQ and skipped".
- Suspected bundles at parse time (title pattern) move straight to `bundle` but with a note that it was auto-detected. User can change to `pending` if it turns out to be a normal game.
- No `confirmed` state needed — the confirmation dialog is a UI gate only. After dialog accepted, `ready` → `imported` immediately.
- Skipped items (`already_in_db`, `out_of_range`, `low_quality`, `bundle`) are excluded from all pipeline screens but included in batch dashboard counts.

### `weekly_batch_raw_pages`

Stores the raw pasted content per page, so it can be reviewed and replaced.

| Column | Type | Notes |
|--------|------|-------|
| id | int PK | |
| batch_id | int FK | |
| console | enum | `switch-1`, `switch-2` |
| list_type | enum | `new`, `upcoming` |
| page_number | int | 1–5 |
| raw_content | text | Original paste |
| parsed_at | timestamp | null = not yet parsed |
| created_at | timestamp | |
| updated_at | timestamp | |

---

## Screen Flow

### Level 1: Batch List
`GET /staff/weekly-batches`

Table: Date | Status | S2 New | S2 Upcoming | S1 New | S1 Upcoming | Action

Each cell shows mini progress: e.g. "8 ready / 3 LQ / 1 bundle".

"New batch" button → date picker → creates batch → redirects to batch dashboard.

---

### Level 2: Batch Dashboard
`GET /staff/weekly-batches/{batchId}`

Four cards (one per list). Each card shows:
- List name + console badge
- Progress bar or step tracker: Raw → URLs → Fetch → Packshots → Categories → Confirm
- Counts: pending / ready / LQ / bundle / imported
- "Continue" button → next incomplete stage for that list

Console-level "Confirm Switch 2" / "Confirm Switch 1" buttons (active once both lists for that console have status `ready`). These go to the confirm screen for that console.

---

### Level 3: Per-list pipeline stages

---

#### Stage 1: Raw Input
`GET /staff/weekly-batches/{batchId}/{console}/{listType}/raw`

**Header:** Shows date range for this list (e.g. "New releases: 18/04/2026 – 24/04/2026").

**Page management panel** (top of screen):
- Shows pages uploaded so far as tabs or a small table: Page 1 ✓ | Page 2 ✓ | [+ Add page]
- Click any page tab → see its raw content (read-only) and a "Replace" or "Remove" button
- "Add page" → new textarea appears, "Save page N" button

**Add/replace page:**
- Textarea (large, monospace) for pasting Nintendo listing data
- "Save page" → stores raw content, triggers parse immediately
- Result summary shown per page: "Parsed 12 entries (8 in range, 4 out of range, 2 already in DB)"
- Full results panel shows three groups: To process | Already in DB | Out of range
- For "to process" items: collection prefix matched, suspected LQ signals flagged (not confirmed yet)

**Missing games check** (shown once all pages for this list are saved):
Queries DB for games on that console within the date range that do NOT appear in the raw data. These may have been delisted, date-changed, or dropped from Nintendo's search results.

Shown as a collapsible "Missing from this week's data" section:

| Title | Current DB Status | Nintendo URL | Action |
|-------|-----------------|-------------|--------|

- "Re-check URL" button per row → queues a single fetch job for that game's stored URL
- Result shown inline: 404 (candidate to delist), redirect loop (310), date changed (new date shown), still exists (flag for manual review)

**Continue button** → goes to URL Collection once at least one page is saved and parsed.

---

#### Stage 2: URL Collection
`GET /staff/weekly-batches/{batchId}/{console}/{listType}/urls`

**Purpose:** Collect Nintendo game page URLs for fetching publisher/players/price.

**Page tabs** at top — one tab per raw page uploaded. User works one page at a time (matches how they scroll the Nintendo site).

Per page, table shows (in raw paste order):

| Title | Release Date | Nintendo URL |
|-------|-------------|--------------|

- Release date shown to help locate game on Nintendo site
- Nintendo URL: text input, placeholder "https://www.nintendo.com/en-gb/..."
- Games already in DB shown greyed out with "Already in DB — skip" label
- "Save URLs for page N" → saves inline
- No validation until Save is clicked

"Continue to Fetch" → available once all non-skipped items on all pages have a URL.

---

#### Stage 3: Fetch & LQ Review
`GET /staff/weekly-batches/{batchId}/{console}/{listType}/fetch`

**Fetch panel** (per page):
- "Fetch page N" button → kicks off background job for that page's URLs
- Status table shows each game: Title | Status (Pending / Queued / Fetching / Done ✓ / Failed ✗)
- Polling every 3–5 seconds to update statuses without full page reload (or manual "Refresh" button initially)
- Throttle: 1–2 second gap between each request in the job

Background job per item:
1. Fetch Nintendo URL
2. Extract: publisher, players, price (if missing), description, Nintendo genres (if richer than listing data)
3. Apply publisher name mapping
4. Look up publisher in `GamesCompany` table (case-insensitive)
5. If found and `is_low_quality = 1` → set `lq_flag = 1`, `lq_publisher_name`
6. If not found → create publisher with `is_low_quality = 0` (new, not LQ unless flagged)
7. Apply LQ signal patterns to flag uncertain cases

**Review panel** (shown once all pages fetched, or can review incrementally):

Three sections:

**Confirmed LQ** (publisher in DB with `is_low_quality = 1`)
- Game title | Publisher | Price | LQ reason
- "Override — keep anyway" checkbox per row (sets `lq_override = 1`)

**Uncertain** (publisher not found, or LQ signals in title/price but publisher not on list)
- Game title | Publisher (raw) | Price | Flag reason | Nintendo URL link
- "Mark LQ" / "Keep" buttons per row

**OK** (publisher found, not LQ, no flags)
- Title | Publisher | Players | Price
- Read-only summary

"Confirm LQ decisions" → saves statuses, moves to Packshot Collection.

---

#### Stage 4: Packshot Collection
`GET /staff/weekly-batches/{batchId}/{console}/{listType}/packshots`

**Only shows non-LQ, non-bundle, non-skipped items.**

Same page-tab approach as URL collection. Per page, table in raw order:

| Title | Release Date | Packshot URL | Preview |
|-------|-------------|--------------|---------|

- Packshot URL: text input
- Preview: once URL entered, small `<img>` thumbnail loads client-side (no server round-trip)
- "Save packshots for page N"

"Continue to Categories" → available once all non-skipped items have a packshot URL.

---

#### Stage 5: Price Review
`GET /staff/weekly-batches/{batchId}/{console}/{listType}/prices`

Only shown if any items have `price_flag = 1`. Otherwise, skip to Categories automatically.

| Title | Flag Reason | Listing Price | Game Page Price | Use This Price |
|-------|------------|---------------|-----------------|----------------|

- Editable price field per row
- "Confirm" checkbox

"Save & continue" → goes to Categories.

---

#### Stage 6: Category Review
`GET /staff/weekly-batches/{batchId}/{console}/{listType}/categories`

**CategorySuggester logic** (runs server-side, results saved to `suggested_category`):

1. **Collection rules** — Arcade Archives/2 → "Arcade"; Console Archives → look at description; Egg Console → platform-specific check
2. **Genre mapping** — lookup table: Nintendo genres → SS categories (e.g. "Shoot 'Em Up" → "Shoot 'em up", "Visual Novel" → "Visual novel", "Rhythm" → "Music")
3. **Title patterns** — "Simulator" keyword → check Simulation subcategories; known series patterns
4. **Publisher rules** — Hamster → "Arcade"
5. **DB history** — look up previous games with same publisher + matching Nintendo genres, check what SS category was assigned → use as suggestion if consistent match found

Confidence levels:
- **High confidence** (collection rule or exact genre match) → shown with "Accept" button
- **Low confidence** (inferred, weak genre match) → shown with ⚠ flag + "Accept" button  
- **No suggestion** → shown with "— needs selection" and dropdown, highlighted in yellow

**Page layout:**

"Accept all suggestions" button at top.

Per game row:
- Title + Nintendo genres + description (collapsible)
- Suggested category badge (or "No suggestion")
- **Accept** / **Reject** buttons
- On Reject → grouped `<optgroup>` dropdown replaces the badge, pre-focused

Once all items have a confirmed category → "Continue to Confirm" button unlocks.

**"Copy for Claude" button** (for games with no suggestion):
- Generates a formatted block with: game title, description, Nintendo genres, and the SS category list
- Includes a pre-written prompt asking Claude to suggest the most specific matching category
- User pastes into a new Claude session, gets back categories, enters them into the dropdowns

---

#### Stage 7: Confirm & Import
`GET /staff/weekly-batches/{batchId}/{console}/{listType}/confirm`

Full review table of all `ready` items for this list:

| Title | Date | Price | Publisher | Players | Category | Collection | Packshot |
|-------|------|-------|-----------|---------|----------|------------|---------|

- Missing required fields highlighted in red
- Read-only (go back to fix)
- Counts: X games to be added, Y publishers to be created

**"Confirm and import"** button — prominent, requires click + confirmation dialog: _"This will add X games to the database. This cannot be undone."_

On submit:
- Runs DB import (same logic as `JsonImportService::executeImport()`)
- Sets `eshop_europe_order` on each created game from the item's `sort_order` value — so the Release Hub already shows games in Nintendo-listing order
- Sets `item_status = imported` and `game_id` per item
- Shows result: games created, publishers created, any errors

---

### Level 4: Console-level Confirm (optional shortcut)
`GET /staff/weekly-batches/{batchId}/confirm/{console}`

Shows both lists for that console side by side. Two "Confirm and import" buttons (one per list), or a single "Import both" if both are fully ready.

---

## Key Services to Build

### `App\Domain\WeeklyBatch\RawTextParser`
Parses the Nintendo listing format into structured records. Handles:
- Title repeated twice → take first
- "Starting from: " prefix
- Sale prices: "£6.02£4.30*" → take first price
- £0.00 → flag
- "Demo available" line to ignore
- ® and ™ removal
- Date extraction (DD/MM/YYYY → Carbon)
- Genre extraction

### `App\Domain\WeeklyBatch\TitleNormaliser`
All documented normalisation rules from context.md:
- ALL CAPS → Title Case
- ™ removal
- Tilde → colon
- Ampersand → "and"
- Single hyphen as separator → colon (with exceptions)
- "EGGCONSOLE" → "Egg Console"
- Minor words lowercase mid-title

### `App\Domain\WeeklyBatch\PublisherNormaliser`
- Publisher name mapping table
- ALL CAPS → Title Case
- CamelCase stays
- DB lookup for existing name

### `App\Domain\WeeklyBatch\CategorySuggester`
- Collection rules
- Genre mapping table
- Publisher rules
- DB history lookup
- Returns: `['category' => '...', 'confidence' => 'high|low|none', 'reason' => '...']`

### `App\Domain\WeeklyBatch\NintendoPageFetcher`
- Fetches game page URL (reuse existing HTTP scraper)
- Extracts publisher, players, price, description
- Returns structured data
- Throttling handled by job queue delay

### `App\Jobs\FetchNintendoGamePage`
- Queued job, one per item
- Delay between jobs: 1–2 seconds
- Updates `fetch_status` on item

---

## Integration with Existing Code

- **Fetching Nintendo pages**: Reuse `App\Domain\Scraper\Base` HTTP infrastructure
- **Import to DB**: Reuse `App\Domain\GameImport\JsonImportService::executeImport()`
- **Publisher lookup/create**: `App\Domain\GamesCompany\Repository`
- **Category lookup**: `App\Domain\Category\Repository::getByName()`
- **Collection lookup**: `App\Domain\GameCollection\Repository::getByLinkTitle()`
- **Duplicate check**: `App\Domain\Game\Repository` — check title + console_id

---

## Phasing

### Phase 1 — Core pipeline
- DB tables + models + repositories
- Batch list + create
- Raw input + parser + per-page management
- URL collection (per page, in order)
- Fetch background jobs + status display
- LQ review
- Packshot collection (per page, in order)
- Category review (with rule-based CategorySuggester + "Copy for Claude" button)
- Confirm & import per list

### Phase 2 — Polish
- Price review screen
- Console-level bulk confirm
- Better fetch status (live polling without full reload)
- "Accept all" memory: if user always accepts Arcade Archives as "Arcade", auto-accept next time

### Phase 3 — Intelligence
- Claude API integration for low-confidence category suggestions
- Publisher review screen before import (review auto-created publishers)
- Batch history / audit view

---

## Context Folder

The original manual Claude Code process context lives at:
`~/Documents/claude-context/side-projects/switch-scores/`

Key files:
- `context.md` — full process documentation, data extraction rules, publisher mappings, LQ signals
- `categories.txt` — full SS category list (always read before suggesting categories)
- `collections.txt` — collection prefixes and link_titles
- `low-quality-publishers.txt` — LQ publisher list for fetch-stage checking

---

## Build Status

### Done
- DB migrations: `weekly_batches`, `weekly_batch_raw_pages`, `weekly_batch_items` (run in prod)
- Models: `WeeklyBatch`, `WeeklyBatchRawPage`, `WeeklyBatchItem`
- Repositories: `WeeklyBatch\Repository`, `WeeklyBatchRawPage\Repository`, `WeeklyBatchItem\Repository`
- Services: `RawTextParser`, `TitleNormaliser`, `ParseService`, `NintendoPageFetcher`
- Routes: all registered under `staff.games.weekly-updates.*`
- Controllers: `WeeklyBatchController` (index/create/store/show), `WeeklyBatchListController` (all list stage methods)
- Views: index, create, show (batch dashboard), list/_stage-nav.twig (7-stage progress stepper)
- **Stage 1 (Raw input):** fully implemented and tested
  - Per-page upload, parse, replace, remove
  - Data preservation on re-parse (snapshot by `title_raw`, restored after re-parse)
  - Block replace/remove if any items already imported
  - Sale price flags auto-cleared on page load (original price was already correct)
  - Exclude/Bundle/LQ/Reset dropdown per item in parsed items table
- **Stage 2 (URL collection):** fully implemented and tested
  - Skipped items (excluded/bundle/lq) moved to separate table at bottom with Reset option
  - Exclude/Bundle/LQ/Reset dropdown per item in main table
- **Stage 3 (Fetch and LQ review):** fully implemented and tested
  - JS-driven, one item per HTTP request with 1.5s delay
  - Publisher normalisation: name map, ALL CAPS → Title Case, DB canonical name used when found
  - LQ tiers: confirmed (auto-skip), uncertain (review), clear (proceed)
  - Exclude/Bundle/LQ/Reset dropdown per item
  - Continue button → Prices stage
- **Stage 4 (Prices):** fully implemented
  - Two-table layout: flagged items (need review) + all other active items (can edit)
  - Inputs always empty — current price shown as read-only text, Nintendo page link per item
  - Sale prices: original (first/higher) price extracted automatically, no flag set
  - Zero prices and "starting from" flagged for manual entry
  - `NintendoPageFetcher` and `TitleNormaliser` improvements this session:
    - Tilde cleanup: remaining `~` replaced with space after subtitle rule
    - Mixed-case word fix: ALL CAPS 5+ letter words → Title Case, lowercase non-minor words → capitalised
- **Stage nav:** 7-stage stepper (Raw → URLs → Fetch → Prices → Packshots → Categories → Confirm)

- **Stage 5 (Packshots):** fully implemented and tested
  - Per-page tables: Title + Nintendo page link, Date, Packshot URL input with live preview
  - Skipped items (excluded/bundle/lq/out_of_range) in separate table
  - JS live preview on URL input
  - Continue → Categories
- **Stage 6 (Categories):** fully implemented (pending test)
  - CategorySuggester: scoring model (DEFINITIVE=4, STRONG=2, WEAK=1; High≥4, Medium 2-3, Low 1, N/A 0)
  - Bypass collections (arcade-archives, arcade-archives-2, aca-neogeo) → immediate High/Arcade
  - Other collections (egg-console, console-archives, pixel-game-maker) → collection history (+2 signal)
  - Specific genres (+4): Visual Novel, Shoot 'Em Up, Rhythm, Roguelike, Pinball, Run-and-Gun, Board Game, Card Game, Trivia, Metroidvania
  - Generic genres (+2): Adventure, Arcade, Fighting, H&F, Music, Party, Platformer, Puzzle, Racing, RPG, Shooter, Simulation, Sports, Strategy, Educational, Survival
  - Ignored genres (0): Action, Communication, Other, Study, Training, Toy, Utility
  - Description/title phrases (+4): "action RPG", "city builder", "tower defence", "roguelike", etc. — land on subcategories
  - Publisher rules (+2): Kemco → RPG, Kairosoft → Management
  - DB history: collection (+2), publisher (+1)
  - Tiebreaker: subcategory preferred over parent when score within 1 point
  - Reason string: supporting signals + conflicts explicitly listed
  - Confidence column: High (green) / Medium (blue) / Low (yellow) / N/A (red)
  - JS modified indicator (pencil badge) when dropdown is changed from original
  - suggestion_accepted tracked at save: 1 = accepted as suggested, 0 = changed
  - Migration: add suggestion_accepted tinyint nullable to weekly_batch_items (run in VM)

### To Do

- **Publisher aliases** — support alternative names for publishers (e.g. "CGI Lab Games" → "CGI Lab"). Needs: `games_company_aliases` table (`company_id`, `alias_name`), lookup in Publishers step and NintendoPageFetcher, UI on company page to manage aliases. Workaround: rename in the publisher name input before clicking Create.
- Stage 7: Confirm and import

---

## Notes

- Switch 2 lists are currently small (often under 10 items each). Switch 1 lists can reach 40 items each across 5 pages.
- The process currently runs on Fridays. The date range is auto-calculated from `batch_date`.
- New releases: look back from batch_date to previous Saturday. Upcoming: look forward to following Sunday.
- Games on "upcoming" this week will appear on "new releases" next week — DB duplicate check handles this automatically.
- LQ games are not imported but are saved in the batch for record-keeping (so we know they were consciously skipped).
- Bundles are not imported but saved similarly.
