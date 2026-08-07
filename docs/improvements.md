# Switch Scores - Potential Improvements

This document tracks potential improvements, features, and enhancements for the Switch Scores project.

**Next ID: 154**

## Numbering rule (added 2026-08-07)

**Always take the number from "Next ID" above, then increment it in the same edit.** Never pick a
number by eyeballing the tables - IDs are spread across five sections plus the Done list, so the
highest number visible in any one section is not the highest in use.

**Every item gets a number, including work that ships straight away.** A Session Log entry is not a
record - it has no ID, so it never reaches the tables or Asana. If something is built and done,
give it an ID and put it in the Done section (see #139).

Three collisions were found and fixed on 2026-08-07 (132, 133 and 134 had each been used twice).
The duplicates were renumbered to 136, 137 and 138; the originals kept their numbers because the
check-in logs already reference them.

---

## Session Log

### 2026-07-26: Insights - Per-URL History Page

**New feature:** `/staff/insights/page?url=...` - full snapshot history for a single page URL.

The history was always being stored (`gsc_page_snapshots` had 220 daily snapshots going back to 2025-12-13, cron at `scripts/switchscores-cron:32`), but the UI only ever showed the latest via `max('snapshot_date')`. This surfaces it.

- Chart of impressions, clicks and avg position over every snapshot (Chart.js 2.8, avg position on a reversed right-hand axis so up = better).
- Latest snapshot compared against 7 and 28 snapshots back. Comparisons are counted in snapshots, not days, because the cron can miss a day - the actual comparison date is shown on each row so it can't mislead.
- Full snapshot table with query counts and top queries, using the standard staff DataTables idiom (`table data-sortable` + `ui/layouts/assets/table-sorting-b5.twig`), 25 per page, sorted by date descending. Top queries column is non-orderable. Null avg position renders blank rather than a dash so DataTables' numeric type detection still applies to that column.
- "History" link added to all three tables on the insights index. Suppressed on the game detail page, which already lists that game's snapshots.

**Prominent caveat on the page:** each snapshot is a trailing 28-day window ending two days before it was taken, so consecutive snapshots overlap by 27 days. It is a trend line, not daily traffic. See #134 for the fix.

**Files changed:**
- `app/Domain/Gsc/Snapshot/Repository/GscPageSnapshotRepository.php` - `getSnapshotsByUrl()`
- `app/Http/Controllers/Staff/InsightsController.php` - `page()`, `buildChanges()`
- `app/Domain/View/Breadcrumbs/StaffBreadcrumbs.php` - `insightsPage()`
- `routes/staff/general.php` - `staff.insights.page`
- `resources/views/staff/insights/page.twig` (new)
- `resources/views/staff/insights/index.twig`, `resources/views/ui/components/gsc/insights-games-table.twig` - History links

**Related:** #133 (date picker on the index), #134 (daily `date`-dimension fetch + backfill).

---

### 2026-04-03: Cross-Console Game Links

**New feature:** #121 - "Also on" section on game pages
- Shows when the same game exists on both Switch 1 and Switch 2
- Matches by `link_title` (same normalized title = same game)
- Displays 75px square packshot with game title, links to other console version
- Located in right sidebar after Game details

**Files changed:**
- `app/Domain/Game/Repository.php` - `getByLinkTitleOnOtherConsole()` method
- `app/Http/Controllers/PublicSite/Games/GameShowController.php` - fetches `OtherConsoleGame`
- `resources/views/public/games/page/show.twig` - "Also on [Console]" section

**Limitation:** Won't match games with different titles (e.g. "Game" vs "Game - Switch 2 Edition"). Future: explicit cross-console linking or edition field (#44).

---

### 2026-03-13: Member Intent System (Public Page CTAs)

**New feature:** Intent system for deferred member actions from public pages
- Public game page CTAs: "Add to collection", "Add to wishlist", "Write a review"
- Handles auth flow: stores intended URL, redirects back after login/register
- Handles verification flow: stores intent in session, shows verification prompt with game context
- **Key fix:** Intent embedded in verification email URL for reliability (survives session issues)
- New controller: `IntentController`, new enum: `MemberIntent`
- New views: `verify-prompt.twig`, `member-collection.twig` (public page partial)

**UX improvements:**
- Login page: "New member? Sign up here" heading, "Create an account" button
- Register page: autofocus on display name field
- Quick review form: `tabindex="-1"` on packshot to skip in tab order

**Related:** #19 (open registration) - now has conversion-focused public CTAs

---

### 2026-02-28: Crawl System Enhancements

**Bug fix:** `UpdateGame::updateDigitalAvailable()` was resetting delisted games back to ACTIVE if they had an override URL. Now only resets games with `last_crawl_status = 200`.

**New feature:** Crawl priority queue
- Added `crawl_priority` boolean field to games table
- JSON import automatically sets `crawl_priority = true` for new games
- "Queue for crawl" button on GameDetail page (Crawl lifecycle tab)
- Crawler prioritises: priority flag → override URLs → never crawled (newest) → null players → oldest crawled
- `[PRIORITY]` tag shown in console output
- Flag cleared after crawling

**Files changed:**
- `app/Domain/DataSource/NintendoCoUk/UpdateGame.php` - only reset to ACTIVE if crawl status 200
- `app/Console/Commands/Game/GameCrawlBatch.php` - priority ordering + display tag
- `app/Domain/GameImport/JsonImportService.php` - set priority on import
- `app/Http/Controllers/Staff/Games/GamesDetailController.php` - queueCrawl() method
- `resources/views/staff/games/detail/show.twig` - queue button + JS
- `routes/staff/games.php` - new route
- `database/migrations/2026_02_28_000001_add_crawl_priority_to_games.php`

---

### 2026-02-20: Initial Review

**Logged & Validated:** 110 improvement ideas from Asana backlog

**Organized by priority:**
- 7 High priority (bugs + foundational changes)
- 34 Medium
- 47 Low
- 7 Needs decision
- 16 Merged/Killed/Done

**Key outcomes:**
- Identified real bugs (#7 tag URLs, #41 title hash, #106 duplicate DataParsedItem)
- Spotted strategic decisions needed (console-split strategy for #3, #4)
- Found Claude Code workflow opportunities (mass tagging, scraping, review imports)
- Designed #110 (unified crawl queue) - see `docs/tasks/110-game-crawl-queue-system.md`

**High priority items:**
1. ~~#7 - Tag URL bug (duplicate link_titles)~~ DONE
2. ~~#8 - Companies search performance (3k records)~~ DONE
3. #11 + #30 - S2 URL handling (do together)
4. ~~#22 - Game status field (Delisted/Soft delete)~~ DONE
5. ~~#106 - Duplicate DataParsedItem bug~~ DONE
6. #110 - Unified game crawl queue

---

## High Priority

| # | Idea | Complexity | Notes | Your Notes |
|---|------|------------|-------|------------|
| 130 | Weekly update tool | High | Replaces manual Claude Code session process | Full pipeline: raw paste → parse → URL collection → Nintendo page fetch → LQ review → packshot collection → category review → import. See `docs/tasks/130-weekly-update-tool.md`. |
| 131 | LQ decision tracking for publishers and keywords | Medium | Build on top of #130 weekly update tool | Track every LQ-related decision made during the weekly update pipeline. When a game is marked LQ, kept despite a flag, or a keyword warning overridden, record the decision against the publisher and keyword. Over time: surface publishers with escalating LQ counts (emerging shovelware), show keyword hit rates (e.g. "SIMULATOR: flagged 12×, kept 2, marked LQ 10"), and flag new publishers with no history. Helps spot LQ culprits before they accumulate many games. Likely a new `weekly_batch_lq_decisions` table + staff report page. |
| 132 | Flag Switch 1 games with price_regular_f / price_sorting_f mismatch | High | Ongoing monitoring needed | Switch 1 games are not covered by the price_sorting_f fix (Switch 2 only). Some S1 games with deluxe editions have price_regular_f inflated to the deluxe price while price_sorting_f holds the correct standard price (e.g. Dark Auction id 17079). Need a SQL query or cron to identify S1 games in data_source_parsed where price_regular_f != price_sorting_f, then flag them with `price-check-deluxe` for manual review. Should run periodically so new games are caught. |
| 152 | Authenticate the public staff-tooling API endpoints | Low | Security - cheap fix, real availability angle | `routes/api.php` only carries `throttle:api` + `bindings`, so seven non-`/v2` routes are callable by anyone on the internet despite being staff tooling (several sit under an `/* Admin */` comment): `/game/get-by-exact-title-match`, `/game/find-by-title`, `/partner/games-company/search`, `/review/site`, `/url/link-text`, `/url/news-url` (and `/game/get-unlinked-data-source-item`, being removed under #67). Each has exactly one or two call sites in staff views, so putting them behind `auth.staff` is low-risk - neighbouring route groups already do exactly that. **Data sensitivity is low** (game and company data that is public anyway), so the case is mostly **availability**: these are unbounded lookup queries on a box with an OOM history. Logging them tells you they're being abused; authenticating them means they can't be. Pairs with the outstanding "security review of member forms and other public forms" item. |
| 148 | API overwrites prices when already set | Medium | Price correctness - recurring theme | The Nintendo API import overwrites a price that has already been set, including manually corrected ones. Today this is held back by `ignore_price` freezes applied by hand, and there are still temporary freezes outstanding from the upgrade-pack work (`price-upgrade-pack`, 6 games) that were only ever meant to last until the parser was fixed. Wanted: the import should not clobber a deliberately set price without an explicit reason. Needs a decision on what "already set" means - manually edited, `ignore_price` set, or any non-null value - since that determines whether this is a small guard or a provenance change on `games.price_eshop`. Note the existing ordering trap: `UpdateGame::updatePrice()` returns early when `ignore_price` is set, *before* writing, so a correction must land before the freeze is applied. Related: #132 (S1 mismatch flagging), and the price bug history in the check-in log. |
| 134 | GSC: fetch daily figures with the `date` dimension, and backfill | Medium | Unblocks true per-day insights; makes #133 honest | Today `SnapshotFetcher` requests a single trailing window (`startDate = now-30`, `endDate = now-2`), so every stored row is a rolling ~28-day aggregate and no real daily figure exists. Adding `date` to `dimensions` returns exact per-day rows in one call. Store daily as the primitive and compute any window (7/28/90) in SQL. Two bonuses: GSC retains ~16 months, so history can be backfilled well before the 2025-12-13 collection start; and genuine day-over-day comparison becomes possible. Watch for: row explosion (`['date','page','query']` multiplies rows by ~28, and the games call already uses `rowLimit: 1000`) - either paginate with `startRow` or split into a `['date','page']` daily series plus a separate lower-frequency call that keeps the query dimension for `top_queries`. Also GSC revises the last ~3 days upward, so the fetcher must re-pull a trailing few days, not just append. Separately, `snapshot_date` currently stores the cron run date, not the date the data covers - worth renaming or storing the window end date alongside. |

---

## Medium Priority

| # | Idea | Complexity | Notes | Your Notes |
|---|------|------------|-------|------------|
| 135 | Single-game add tool (weekly update pipeline for one game) | Medium | Build on top of #130 weekly update tool | Occasionally a game is found that was missed from being added - the root cause is fixed, so this is a low-volume, ad-hoc need, not a recurring batch. It can be done today via the Release Hub, but that means assembling the game by hand. Wanted: something closer to the #130 weekly update flow but scoped to a single game - paste or enter one Nintendo URL (or title), then run the same steps the batch pipeline runs (Nintendo page fetch, LQ check, packshot collection, category assignment, import) with the same defaults and validation, ending in one imported game. Value is consistency: a game added this way should be indistinguishable from one that came through the weekly batch, rather than depending on remembering which fields to set manually. Open questions: whether it reuses the `weekly_batch_*` tables as a batch-of-one (cheapest, keeps one code path, but pollutes batch reporting) or gets a separate lightweight path; and whether `sort_order` / `eshop_europe_order` can be derived sensibly for a single game outside a listing. |
| 136 | Steam-backed news content (editorial auto-generation) | Medium | Builds on #90 Steam infrastructure + existing feature queue system. **Renumbered from 132 on 2026-08-07** (clashed with the S1 price mismatch item). Stage 1 is BUILT AND SHIPPED - `FeatureQueueBucket::UNRANKED_STEAM_GEM`, `FeatureQueueEnqueue --min-steam-score/--category-id`, staff news dashboard + bucket views. Outstanding vs the task doc: decision 5's dedicated `/staff/news/steam-gems` page (currently the generic `staff.news.bucket` route), and the stage 2 parent-category fallback. | New `unranked-steam-gem` bucket: selects games with 0–2 Switch Scores reviews + Steam `review_score >= 8`. Extends `features:enqueue` to use Steam priority signal. Auto-generates `/news` draft on cadence via existing `generateBucketDraft()`. Staff dashboard link to trigger. See `docs/tasks/136-steam-backed-news-content.md`. (Note: this column says `review_score >= 8`, but decision 2 in the task doc lowered the starting threshold to `>= 7` given sparse Steam coverage, to be raised to 8 once the pool is deeper - the task doc is authoritative.) |
| 133 | Insights: date picker to view an earlier snapshot | Low | Depends on #134 to be genuinely useful | Let `/staff/insights` show the tables for any past `snapshot_date` rather than only the latest (the repo currently hard-codes `max('snapshot_date')`). The data is already there - 220 daily snapshots since 2025-12-13. Caveat that makes this low value on its own: with the current rolling-window fetch, going back one day shows the same 28-day window shifted by one, overlapping the previous by 27 days, not that day's traffic. If built before #134, the UI must say "28 days ending X" and never "traffic on X". Note you cannot recover daily figures by differencing consecutive snapshots - the delta is (day added) minus (day dropped 30 days back), which is one equation with two unknowns. |
| 151 | Extend API request logging to the remaining API routes and to V2 | Low | Middleware already supports it - two small changes | `log.api` currently wraps only the four V1 game endpoints. Extend it to (a) the seven other non-`/v2` routes and (b) the `auth:sanctum` V2 group. The middleware was deliberately built to take a version marker (`log.api:V1`) so it could be expanded without change, so this is close to a one-line addition each. Most valuable **now**, while V2 traffic is near zero and a baseline is cheap. Also directly serves Phase 3 of `docs/api-v1-deprecation-plan.md`, which says to check active token count before removing `list-all` - per-endpoint logs answer that far better than Sanctum's `last_used_at`. **Log internal calls too, and classify at read time** rather than filtering at write time: `token_id` present means an API consumer, absent-plus-staff-session means internal. You can always filter noise out of a log; you can't recover a request you never recorded. A stored `source` column is deliberately NOT proposed - it bakes a judgement into the data that can be derived instead. Volume is not a concern: the chatty routes fire from staff screens, so the table grows with one person's clicking, not with public traffic. |
| 140 | De-listed games: display issues on search and game pages | Medium | Public-facing correctness. Follows on from #22 (game status) | Three symptoms, all on de-listed games that were previously ranked. **(a)** Search results show a nonsense review count - "10/3" reviews - e.g. `/games/search?search_keywords=a+normal+lost+phone`. **(b)** The same search page has no "De-listed" status badge, so there is no signal the game is gone. **(c)** The game page (`/games/586/a-normal-lost-phone`) shows "TBC" where it could show the average score, just without a rank - the score is known and still useful, it is only the ranking that no longer applies. Worth treating (c) as a deliberate decision: de-listed games keep their score but lose their rank. Possibly splittable into a search-page item and a game-page item if they get worked separately. |
| 141 | Game flags: quick way to add/remove pre-made flags | Medium | Workflow speed on a weekly task | Flags (`price-check-deluxe`, `price-self-heal`, `price-recheck`, `price-sale-monitor`, `price-upgrade-pack`) are the active mechanism for the price review passes, and the cohorts run to hundreds of games. Adding and removing them is currently slow. Wanted: add/remove a pre-made flag in one click from the places the review work actually happens (game detail, and ideally the flag list views), rather than through a general edit form. |
| 143 | Browse by date: some buy links missing or wrong colour | Medium | Public-facing bug | On `/c/switch-1/2026/07` some buy links are missing entirely and others render in the wrong colour. Needs a pass to establish whether the missing links are a data gap (no store link on the game) or a template condition, since the fix differs. The colour issue is likely a styling condition that hasn't kept up with the newer layouts. |
| 146 | Staff list pages should respect the game status field | Medium | Gap in the #22 rollout, not a single-page bug | `/staff/games/list/no-category-all` still shows soft-deleted records. **Confirmed in code:** `GameLists\Repository::noCategoryAll()` is `Game::whereNull('category_id')->get()` with no status filter at all, and neighbouring methods (`noCategoryWithCollection`, `noCategoryWithReviews`) are the same shape. `App\Domain\GameLists\MissingCategory.php` has **zero** status references. #22 added `active()` / `delisted()` scopes and updated the repos it touched, but the GameLists repository was largely missed. Treat as an audit of every list method rather than a one-line fix, and decide the default per list - most staff lists are work queues, so soft-deleted records should almost always be excluded. |
| 147 | Priority queue when adding new games (not just JSON) | Medium | Extends #110 crawl priority | `crawl_priority` and the "Queue for crawl" button exist, and JSON imports are auto-prioritised (added under #110 on 2026-02-28). Games added by other routes - the Release Hub, and the single-game tool in #135 - do not get the same treatment, so they wait in the ordinary crawl queue despite being the ones someone is actively working on. Wanted: any newly added game gets queued at priority regardless of how it was added. |
| 149 | Weekly update: adding missed items puts records in the wrong status | Medium | Blocks the weekly run until worked around | Adding games missed from previous weeks into the current batch left them in a state where the Fetch step reported no URL. Workaround found on the day: go to URLs, click Save, then continue to Fetch - which suggests the records are created at a status the Fetch step doesn't consider ready, and that re-saving the URL advances them. Needs the status transition for late-added items traced against the normal path through the #130 pipeline. The workaround is reliable, so this is a correctness/friction bug rather than a blocker. |
| 128 | Review and clean up data sources | Medium | Do before adding new sources | Audit source IDs 1–5: update names (ID 2 is nintendo.com/en-gb not .co.uk), assess what to keep vs retire, clean up ~3k orphaned Wikipedia rows (ID 4), document what each source is before adding US Nintendo site or others. |
| 129 | Remove Genres from Differences section | Low | Genres never map cleanly | Remove Genres diff link from DS dashboard, remove associated controller/query logic and route. Genres from API are reference only, not for copying over. |
| 5 | Change category to allow drill-down by tag | Medium | `gamesByCategoryAndTag()` exists but no UI | Categories collapsed into tags (e.g. Picross under Puzzle). 1 category per game, multiple tags. Show only tags with games in that category. Useful for discovery. |
| 6 | Tags: add support for layout v2 | Killed | 2026-05-17 | → Superseded by /browse/tag which uses the new merged console layout. Old /c/{console}/tag routes redirect to /browse/tag. |
| 9 | Add data checks as global lists | Medium | IntegrityCheck methods exist - need staff pages | GameDetail checks (category, players, price etc) rolled up to dashboard. Show totals, click through to fix. Could use Claude to scrape/backfill. |
| 123 | Raw item detail page: tidy up layout | Low | Text overflows off the page currently | Fix layout so long field values wrap correctly and the page is usable. |
| 124 | Parsed items: search, filters, and list view | Medium | No general parsed items list page exists | Create a searchable/filterable list page for parsed items. Unlinked/ignored pages already exist as filtered views. |
| 125 | Parsed item detail page | Medium | No detail page exists for parsed items | Create a detail page showing all fields for a parsed item. |
| 126 | Link to raw/parsed detail pages from Game Detail and Nintendo API list views | Medium | Partial — Game Detail shows parsed data but no links to detail pages | Add links to raw and parsed item detail pages from the Game Detail data sources tab and from any Nintendo API list views. |
| 17 | Action list for games without a custom description | Low | Simple query + list view | On-page descriptions for SEO (thin content fix). Not copied from Nintendo to avoid duplicate content. Check if also in meta. |
| 28 | Update New releases page to new layout | Medium | V1 template exists; create v2 with stats/featured sections | High traffic page. Simple list currently. Could add affiliate links, more info. Not same as category v2. |
| 29 | Update homepage to new layout | Medium | Refactor to unified bindings + v2 layout | Needs refresh, been same for a while. Open to ideas. Could incorporate ones-to-watch, featured, etc. |
| 42 | Event/log when game hits 3 reviews | Medium | No event system for review milestones; needs dispatcher | 3 reviews = ranking threshold. Surface "newly ranked" across homepage, reviews, members, staff. |
| 44 | Add edition field to games + link S1/S2 versions | Medium-High | Requires migration, model, editor UI, linking | For "Switch 2 Edition" games. Link to S1 version. Helps count unique games. Becoming more common. **Note:** Weekly update converts "Nintendo Switch 2 Edition" → "(Switch 2)" in titles, which the cleanup command then removes. When implementing, re-check all existing S2 listings to identify editions. |
| 49 | Games companies: create dashboard and missing data filters | Medium | Dashboard exists; add missing data filter queries | For outreach - find companies to contact. Some may exist already - needs review. |
| 50 | GH118: public companies page improvements | Medium | Public profile exists; enhance layout/data | Searchable list, recent games by publisher, avg score, "Claim this page" CTA, show if company is engaged. |
| 52 | Games company signup | Low | Already implemented and working | MVP done. Full flow: status on submissions, staff approve/deny, create company+user, link, notify. Handle duplicates, validate access to existing companies. |
| 59 | Set up eShopperReviews as a reviewer | Low | Add ReviewSite entry + feed config | Their data sucks but lots of reviews. Custom scraper needed. One-time scrape to JSON + review import tool. Could reuse for future reviewers! |
| 61 | GH111: more names for games companies | Medium | Need UI for alternative names | Match name variations to one company. Doing some via Claude but UI would help. |
| 62 | New process status: Content does not meet inclusion criteria | Medium | Add new status constant + update logic | Consolidates similar statuses. Needs data fix for existing records. |
| 66 | Submit quick review without signing up | High | Auth system requires user; needs guest flow | Spam risk but need reviews. WordPress-style: Name/Email, cookie, optional auto-account. Low friction. Do even with good signup. |
| 69 | Fix Digitally Downloaded Feedburner review links | Low | Update PartnerFeedLink URL | Older review URLs are dead. Need scraping to find actual URLs. Claude can help. |
| 77 | Video URL: scrape from Nintendo pages | Medium | Extend #110 crawl system | Scrape official video URL from Nintendo UK pages during crawl. |
| 116 | Publisher change monitoring | Medium | New feature | Watch for publisher name changes on Nintendo pages. Challenge: distinguish between publisher renames (update existing) vs legitimate publisher changes (new assignment). Log changes for staff review rather than auto-updating. |
| 87 | GH156: save smaller versions of images for landing pages | High | Requires ImageMagick + CDN strategy | Big images slow pages. But don't want fuzzy images in larger spaces. Balance needed. |
| 111 | Refactor App\Domain folder structure | Medium-High | Do in stages | Split large Repository.php files into smaller focused files. Only have folders in App\Domain that map to models. Phase out App\Domain\Game\Repository.php (already started). Merge App\Domain\GameStats into App\Domain\Game. Pattern: App\Domain\Game\Repository\* for sub-repositories. |
| 112 | Standardise staff page section headers | Low | Twig macro exists | Mix of hard-coded HTML and reusable heading.renderSlick(). Audit staff views and consolidate to use the macro consistently. |
| 114 | Retire App\Services folder | Medium | 13 files to migrate | Move all Services to Domain: DataSources/NintendoCoUk→DataSource/NintendoCoUk, Game/*→Game/, Feed/*→Feed/, DataQuality→Game or new folder, Eshop/*→DataSource/. Update namespaces, imports, and delete empty folders. Related to #111. |
| 115 | Claude-assisted game tagging | Medium | Discovery/clustering goal | Help tag games to improve discoverability. Focus on Viewpoint, Visual style, Game type. Phase 1: keyword matching (title contains "Solitaire" → Solitaire tag). Phase 2: Nintendo genres_json mapping. Phase 3: interactive batch suggestions. Build artisan command to export "games with keyword X but missing tag Y". |
| 122 | Email members when quick review approved | Low | Member retention | Notify members when their quick review is approved. Could bring members back after initial signup. Of 5 April 2026 signups, 2 submitted quick reviews but none returned since - this could re-engage them. Simple: trigger email on review status change to approved. |

---

## Low Priority

| # | Idea | Complexity | Notes | Your Notes |
|---|------|------------|-------|------------|
| 2 | Bulk add tag to games with search string (e.g. Solitaire) | Medium | No bulk tag UI - needs new controller/view | Explore using Claude for mass tagging instead of building UI |
| 138 | Staff-specific error/404 page template | Low | **Renumbered from 134 on 2026-08-07** (clashed with the GSC daily-figures item). Related to #32 (public 404). Needs an auth-aware error view | Hitting the public 404/error layout while logged in as staff is jarring. Show a staff-flavoured error page (staff layout + links back into admin) when an authenticated staff user hits an error. |
| 153 | Prune / retention policy for `api_request_log` | Low | Decide now, not at migration time | The `api_request_log` table has no retention policy, so it grows forever. Add an artisan command to drop rows older than N months, and wire it into the cron. Small job, but worth deciding the window **now** rather than discovering an unbounded table during the infrared-switch migration. Related: #151 (which increases what gets written). |
| 142 | Link New releases to Browse by date (both consoles) | Low | Internal linking - has an SEO angle | New releases should link through to the Browse by date pages for the current and previous month, for both consoles (e.g. `/c/switch-1/2026/07`). Currently there is no route from one to the other. Worth considering as more than a nav tweak: New releases is a high-traffic page (#28), and the browse-by-date pages sit in the "crawled - currently not indexed" space the SEO work is trying to reduce, so internal links from a strong page are exactly the signal those pages lack. |
| 145 | Game detail: GSC snapshot tab should show when the data is old | Low | Clarity, not correctness | The GSC snapshot tab shows the latest stored snapshot for the game, which is correct behaviour, but when there is no recent data it silently shows figures that may be months old with nothing marking them as stale. Wanted: show the snapshot date prominently and visibly flag it when it is beyond some threshold. Cheap version is a date plus an "as of" label; better version greys it out or warns past N days. Related to #139 (per-URL history page), which already made a point of showing the actual comparison date so figures can't mislead - same principle. |
| 150 | Weekly update: adding missed items UX is poor | Low | Fix the first problem and the second stops mattering | Two problems when adding items missed from previous weeks: **(1)** after adding one, the page reloads and returns to the top, losing your place; **(2)** multiple items have to be added one at a time. Ben's note: 2 isn't a problem if we just fix 1 - so the scoped fix is to preserve scroll position (or add without a full reload) rather than to build a bulk-add flow. Related to #149, which is the status bug hit during the same task. |
| 13 | Slow queries: stats on Browse by date page | Medium | Heavy stats queries - needs caching/indexes | From pre-Cloudflare logging. May be less urgent now. Could add Redis caching for big queries. Review needed. |
| 16 | Ones to watch: show a list in admin and public | Medium | `one_to_watch` field exists - need views | Manual flag on games. Placement TBD - Members, homepage, or /switch-1/ landing pages. Includes #21. |
| 20 | Move Stats dashboard to Staff dashboard | Low | Stats dashboard exists; reuse queries in staff view | Consolidation - not much on stats dashboard currently. |
| 23 | Split out tag verified into one field per tag category | Killed | 2026-05-16 | → Dropped `tags_verification` field entirely. Categorisation dashboard already shows tag category progress per-category. |
| 32 | Improve 404 page with more useful links | Low | Custom view + related game suggestions | Not much on it currently. Add helpful links to guide users. |
| 34 | Change PlayStatus to an Enum | Medium | 7 constants + factory methods; test coverage impact | IDE autocompletion benefit. Code cleanliness. |
| 35 | Change FormatOptions to an Enum | Low | Only 5 format constants; simple extraction | IDE autocompletion benefit. |
| 36 | Change VideoType to an Enum | Low | Only 3 constants in Game model | IDE autocompletion benefit. |
| 39 | GH123: Migrate staff pages to Bootstrap 5 | Done | 2026-04-23 | → Done section |
| 40 | Replace GameRepository->getAll with dropdown search | High | Unbounded query; needs API + dropdown UI | API might use it. API underused - could limit there. Not related to #8 (that's companies). |
| 43 | Add Switch 2 to game search | Low | Console scoping exists; add filter parameter | Needs investigation - likely mixing all games without distinguishing. |
| 46 | Split game list by console | Low | Console filters exist in repository; expose in UI | API ticket - add console filter to API. |
| 47 | Split game list by year | Low | Year searching exists; add grouping in templates | API ticket - add year filter to API. |
| 57 | Caching: user games collection IDs | Low | Cache via Redis/cache layer | Performance optimization. |
| 58 | Show ranked total and % for standard quality games | Medium | GameQualityScore exists; add ranking query | Useful stats - need to decide placement. |
| 63 | Bulk edit: games without store links | Low | Bulk editor exists; add filter | Have tool but one at a time. Claude could help with bulk. |
| 64 | GH41: allow users to select categories they like | Medium | Need new preferences table + UI | Nice if linked to other features. |
| 68 | Roll out table-row-stat to all staff pages | Medium | Template rollout to staff-b5 pages | May have newer Twig macro approach. Same intent. |
| 73 | Related games: one list? (Manual/category/collection/series) | Medium | Unify fragmented related games | 3 sections stacked + manual + S1/S2 editions = too much. Smarter layout needed, not necessarily one box. |
| 74 | Featured games: rotate | Medium | FeaturedGame model exists; add rotation logic | Only in Members (latest 3). Ties to displaying featured elsewhere. May overlap with existing ticket. |
| 75 | Daily stats for monitoring | Medium | No model; create new stats table | Most exist already: games, review links, ranked, without categories, unlinked, N.co.uk parsed. |
| 77 | Video URL: scrape from Nintendo pages | Moved | 2026-03-01 | → Medium priority, extend crawl system |
| 78 | 404 checker | Merged | 2026-02-23 | → #110 |
| 79 | Unranked for members | Low | Add unranked filter to member views | May push members to review games. |
| 80 | Twitter signup / autogenerated email address | High | OAuth exists; needs email auto-gen flow | Tech debt. Low value if removing Twitter. But useful if multiple login methods later. Hold for now. |
| 81 | Review links: remove ability to change site id | Low | Add validation to prevent changes | Only for incorrect imports. Keep for now. |
| 82 | Games collection: hide Format and Hours played when adding; show on edit | Low | UI form logic tweaks | UX help. Newer add-to-collection page exists but not complete yet. |
| 83 | Games collection: set owned date to today, custom date, or ignore | Low | Add date picker with presets | UX help. |
| 84 | Move PartnerUpdateStats > Reviews | Low | Domain folder refactoring | Might be a command - needs checking. |
| 85 | Delete review draft | Low | Add delete endpoint with auth checks | For members. |
| 86 | GH81: use Youtube API to search for videos | High | Requires API key + quota management | Helpful but #77 gives "official" videos. Consider both. |
| 88 | GH141: Generate images for "by collection" like "by series" | High | No image generation service | Series page is long/slow. Collections smaller - more suitable. Fiddly but nice results. |
| 89 | GH56: add more signup methods | Medium | OAuth exists; add providers. *Related to #80* | Maybe just Google. Remove Twitter. Keep email/pw. Risk: multiple accounts if users forget which method. Hard problem. |
| 93 | Raw items: replace list view with search | Medium | Currently loads all items at once — DB intensive | Replace full list with a search/filter UI with pagination. |
| 113 | Game status: surface API vs manual conflicts | Closed | 2026-04-23 | → Addressed by #127 audit log (conflict event type) |
| 96 | GH124: Allow games companies to update contact details | Done | 2026-04-23 | → Done section |
| 97 | Show recent quick reviews on homepage/Reviews homepage | Low | Already shown on Community page | Need more reviews first. Already on Members page. |
| 99 | GH30: member profiles | High | No profile model; requires full implementation | Worth doing once more members. Can be lightweight initially. |
| 102 | Onboarding: dismissable notice banner for logged in users | Medium | Need notification model + dismissal | Nice but low priority without new signups. |
| 103 | Upload / edit avatar | High | No avatar field; requires full implementation | Useful as members grow. |
| 105 | Review provenance: record source + user for drafts and links | Medium | Fix mislabelled source, populate user ids, reconcile review_type | Rescoped 2026-07-19 - bigger than the original title. Prerequisite for feed health. Plan: `docs/tasks/105-review-provenance.md` |

---

## Needs Decision

| # | Idea | Complexity | Notes | Your Notes |
|---|------|------------|-------|------------|
| 3 | Change series page to be both consoles | Done | 2026-05-17 | /browse/series live with merged Switch 1+2 view, console filter as query param. Old /c/{console}/series routes redirect. Includes #26, #71. |
| 4 | Change collection page to be both consoles | Done | 2026-05-17 | /browse/collection live with merged Switch 1+2 view, console filter as query param. Old /c/{console}/collection routes redirect. Includes #27. |
| 56 | Lists: New additions/DB entries | Killed | 2026-04-23 | Low value vs new releases page. Surprise releases idea can be a new task if needed. |
| 98 | Review sites: link to all reviews by a partner (public) | Low | Add public page listing reviews | Only show latest 20-25 per partner. NLife has 3000+. Rethink or revamp partner profile pages instead? Maybe kill and create new tasks. |
| 108 | Scrape Developer from US Nintendo pages | Medium | UK pages don't have Developer, US does | Would need to add US URLs first |
| 109 | Check for dead Nintendo URLs | Merged | 2026-02-23 | → #110 |

---

## Merged / Killed / Done

| # | Idea | Status | Date | Notes |
|---|------|--------|------|-------|
| 144 | Game detail: remove "Tags verif." from data checks | Done | 2026-08-07 | The `tags_verification` column was dropped under #23 on 2026-05-16, but the Data checks block still rendered a "Tags verif." row reading `$game->tagss_verification` (**typo, double s**) - a misspelled property on a column that no longer existed, so the check was null and rendered as **failing on every game, permanently**. Removed the array entry from `GamesDetailController.php`. "Category verif." left in place; `category_verification` still exists and that check is genuine. |
| 137 | Delete the legacy `wos` staff theme | Done | 2026-08-07 | Confirmed orphaned before deleting: `theme/wos/staff/base.twig` was referenced only by `clean-narrow.twig` and `clean-wide.twig`, and **nothing referenced those two**. Removed all 11 files under `resources/views/theme/wos/staff/` (base, breadcrumbs, clean-narrow, clean-wide, and 7 `nav-top/` partials). Staff is fully on `theme/staff-b5`. Removes the trap where new nav links were only ever added to the B5 version. Renumbered from 133 earlier the same day. |
| 67 | Remove getUnlinkedDataSourceItem from Api/Game/TitleMatch | Done | 2026-08-07 | Dead code confirmed, not assumed: no callers in views, JS, commands or tests - only the route definition itself. Its logic was already duplicated inside `getByExactTitleMatch()` (the `getUnlinkedByTitle` lookup), so nothing was lost. Removed the method and the `/api/game/get-unlinked-data-source-item` route. **This was a public unauthenticated V1-era route**, so removal is technically breaking for any external consumer; agreed acceptable as it's V1 and the API is underused. Corrects `docs/api-v1-deprecation-plan.md`, which had grouped it with its live siblings as staff tooling that stays. |
| 139 | Insights: per-URL history page | Done | 2026-07-26 | `/staff/insights/page?url=...` - full snapshot history for a single page URL. Chart of impressions/clicks/avg position, comparisons against 7 and 28 snapshots back (counted in snapshots, not days, with the actual date shown), full snapshot table with top queries. "History" links added to the insights index tables. **Number assigned retrospectively on 2026-08-07** - the work shipped with a Session Log entry but no ID, so it was invisible to the tables and to Asana. Carries a prominent caveat that each snapshot is a trailing 28-day window (see #134). |
| 90 | Link to Steam and reviews | Done | 2026-05-04 | Staff screen to track Steam status per game (not checked / linked / not on Steam). Auto-sync Steam review summary on link. Weekly cron (`game:sync-steam-reviews`). Public game page shows Steam review summary with colour-coded sentiment. LQ games excluded throughout. |
| 127 | Smarter Nintendo.co.uk import | Done | 2026-04-23 | Upsert approach with content hashing. Detects new/changed/delisted games. Full audit log (`data_source_import_log`) with event types added/updated/delisted/conflict. Import run tracking (`data_source_import_runs`). Staff dashboard shows 21-day run history; detail page shows paginated log entries with filter by event type. Closes #113. |
| 96 | Allow games companies to update contact details | Done | 2026-04-23 | Edit profile page allows updating email, URL, and social links. |
| 14+15 | Data source items: staff pages | Superseded | 2026-04-23 | Replaced by #93, #123, #124, #125, #126. |
| 39 | Migrate staff pages to Bootstrap 5 | Done | 2026-04-23 | All staff views migrated. Remaining `form-control` on select elements fixed 2026-04-23. |
| 94 | Quick reviews: approve/reject workflow | Done | 2026-04-23 | Replaced Edit with Approve/Reject buttons on staff list. STATUS_INACTIVE renamed to STATUS_REJECTED (value 9→2) with data migration. Edit screen removed. Review body shown inline on list view. Members only see Pending/Active reviews (not Rejected). |
| 11+30 | Switch 2 game URLs + per-console title uniqueness | Done | 2026-04-03 | S2 games use `/switch-2/games/{id}/{slug}`, S1 unchanged at `/games/{id}/{slug}`. Title uniqueness now per-console (`game_title_hashes.console_id`). `game_url()` Twig function is console-aware. Cleanup command `game:cleanup-switch2-titles` removes "(Switch 2)" suffix. See `docs/tasks/011-030-switch-2-game-urls.md`. Out of scope: S1 migration to `/switch-1/...`, dropping `/c/` prefix. |
| 3 | Change series/collection/category/tag pages to merged console view | Done | 2026-05-17 | New /browse/ section live: /browse/category, /browse/series, /browse/collection, /browse/tag. Merged Switch 1+2 with console filter as query param. Console labels on game cards. Browse dropdown in top nav. 301 redirects from all old /c/{console}/category|series|collection|tag routes. Canonical tags on list pages. Sitemap updated. Includes #4, #6, #26, #27, #71. |
| 51 | View games company signups | Done | 2026-03-22 | Staff list page at `/staff/games-company-signups`. Shows contact info, existing/new company details, games list. Linked from staff dashboard. |
| 100 | GH32: Games collection - quick status changes | Done | 2026-03-13 | Superseded by redesigned add/edit collection pages with play status button tiles (#82/#83). |
| 19 | Make registration open + intent system | Done | 2026-03-13 | Open registration live since 2026-03-08. Intent system added 2026-03-13: public game page CTAs (Add to collection/wishlist, Write review) that work through auth + verification flow. Intent embedded in verification URL for reliability. Login/register UX improved. |
| 120 | Member nav restructure with secondary nav bar | Done | 2026-03-08 | Primary nav simplified to top-level links (Members, Developers, Reviewers, Games companies, Staff, Logout) with active state. Secondary nav (lighter colour) shows contextual sub-links per section. Each section has Dashboard link. Also migrated 8 collection pages from B3 to B5. |
| 53 | Allow members to edit display name, email, and pw | Done | 2026-03-08 | Settings page at `/members/settings` with display name (all users), email and password (email login users only). Added "Edit your details" button to dashboard and Logout link to nav. |
| 119 | Crawler hangs on redirect loops | Done | 2026-03-08 | Added `setMaxRedirects(3)` to HttpBrowser in crawl commands and Base scraper. Catches `\LogicException` for redirect limit, marks game with status 310, continues batch. Also added 30s timeout. Affects `GameCrawlBatch`, `GameCrawlUrl`, and `Base` scraper (used by editor page). |
| 118 | Page title has Switch Scores twice | Done | 2026-03-08 | `PublicPageBuilder::titleSuffix()` returned "Switch Scores", then base template appended "| Switch Scores". Fixed by returning empty suffix for public pages; `buildTopTitle()` now handles empty suffix gracefully. |
| 117 | Migrate Nintendo URLs from .co.uk to .com/en-gb | Done | 2026-03-08 | Updated 3589 `nintendo_store_url_override` URLs from `nintendo.co.uk` to `nintendo.com/en-gb`. Eliminates 308 redirect hop on every crawl. Also found and cleared 1 bad 3DS URL. `data_source_parsed.url` was already using path format. |
| 70 | Re-download hi-res header images | Done | 2026-03-01 | Extended crawl system to check header image sizes. Compares remote Content-Length via HEAD request against local file size. Re-downloads if different. Added to both `game:crawl` and `game:crawl-batch` commands. Uses og:image meta tag for URL. Output: `[image updated] X → Y bytes`. **2026-03-08:** Added dated filenames (`hdr-{id}-{title}-{YYMMDD}.jpg`) to bypass Cloudflare's 1-year cache; deletes old image after successful download. |
| 33 | Remove old image fields | Done | 2026-03-01 | Removed legacy `boxart_square_url` and `boxart_header_image` fields. Only 1 game had values, and it already had images in the new fields. No `/img/games/` folder exists anymore. Cleaned up: Game model, ImageHelper, GameBuilder, GameDirector, GameFactory, unit tests. Migration to drop columns. |
| 10 | Scrape publisher name, players, and other info from Nintendo URL | Done | 2026-03-01 | Superseded by #95/#107 for players; publisher handled in separate import project. New #116 created for publisher change monitoring. |
| 95 | GH76: multiplayer options | Done | 2026-02-27 | Extended crawl system to scrape players (local/wireless/online), multiplayer mode, features (online play, local multiplayer, play modes). New `game_scraped_data` table, `NintendoCoUkGameData` scraper, new game fields (`multiplayer_mode`, `has_online_play`, `has_local_multiplayer`, `play_mode_tv/tabletop/handheld`). Shown on staff detail + public game pages. Related: #107. |
| 107 | Store Local vs Online player counts separately | Done | 2026-02-27 | Implemented with #95. `game_scraped_data` table stores `players_local`, `players_wireless`, `players_online` separately. Combined into `games.players` field (e.g., "1-8"). |
| 110 | Game crawl POC | Done | 2026-02-23 | `game:crawl` + `game:crawl-batch` commands, `last_crawled_at` + `last_crawl_status` fields, `game_crawl_lifecycle` table, dashboard stats with links to list pages, GameDetail tab, cron entry (100/night). See `docs/tasks/110-game-crawl-queue-system.md`. **2026-02-28:** Added `crawl_priority` field + "Queue for crawl" button; JSON imports auto-prioritised; fixed `UpdateGame` resetting delisted games. |
| 22 | Add game status for Delisted, (Soft) deleted | Done | 2026-02-21 | GameStatus enum (ACTIVE/DELISTED/SOFT_DELETED), all repos updated to use active()/delisted() scopes, staff inline editor, 410 for soft_deleted, API safeguards. See `docs/tasks/022-game-status-field.md` |
| 31 | Hold deleted URLs; send 410 status to Google | Done | 2026-02-21 | Implemented via #22 - soft_deleted games return 410 status, `errors/410.twig` created |
| 18 | Tag categories: show groups on categorisation dashboard | Done | 2026-02-21 | Reorganized dashboard layout, added progress bars for each tag category, excludes low quality/de-listed games |
| 41 | Update the title hash when editing a game's title | Done | 2026-02-21 | Auto-creates new title hash when title changes; validates against other games; added repository methods |
| 65 | Game list: by games company | Done | 2026-02-21 | Added by-company list type with CSV export, limited company show page to 10 recent games with total count, created reusable console-badge.twig macro |
| 101 | Quick reviews: char count in content box | Done | 2026-02-21 | Added live character counter with maxlength and color warnings |
| 1 | Staff dashboard: recently added is Switch 1 only | Done | 2026-02-21 | Added S1/S2 console badges to recently added games list |
| 106 | BUG: Duplicate DataParsedItem records for same console | Done | 2026-02-21 | Switch 1 query wildcard was matching Switch 2 games; added exclusion filter |
| 8 | Games companies: search without needing to view all | Done | 2026-02-21 | Replaced full list with search page + quick filter links |
| 37 | Replace substr with str_starts_with | Done | 2026-02-21 | PHP 8 modernization - 12 replacements across 7 files |
| 38 | Replace strpos with str_contains | Done | 2026-02-21 | PHP 8 modernization - 7 replacements across 6 files |
| 7 | Tags: don't allow two with the same URL | Done | 2026-02-21 | Added unique constraint + Laravel validation in TagController |
| 12 | Table sorting is broken on one staff page | Done | 2026-02-21 | Fixed missing Console column in DataTables config (list-scripts.twig) |
| 21 | Show "one to watch" on site | Merged | 2026-02-20 | → #16 |
| 24 | Rework tag pages for v2 layout | Merged | 2026-02-20 | → #6 |
| 25 | Allow drill down by tag within a category | Merged | 2026-02-20 | → #5 |
| 26 | Update series pages to have Switch 1/2 in single list | Merged | 2026-02-20 | → #3 |
| 27 | Update collection pages to have Switch 1/2 in single list | Merged | 2026-02-20 | → #4 |
| 45 | Games with multiple editions: link together (S1/S2) | Merged | 2026-02-20 | → #44 |
| 48 | Search by games companies without going into the list | Merged | 2026-02-20 | → #8 |
| 54 | Look into splitting low quality games from browse pages | Done | 2026-02-20 | Covered by v2 templates |
| 55 | Send invite codes from requests screen | Superseded | 2026-02-20 | By #19 (open registration) |
| 60 | Auto-assignment rules | Killed | 2026-02-20 | Doing via Claude Code already |
| 71 | View all in series/category: split by Switch 1/2 | Merged | 2026-02-20 | → #3 |
| 72 | Homepage: console split for Recent top rated | Killed | 2026-02-20 | Yearly done, console name shown above images |
| 76 | Bulk edit: publisher link should go to staff | Killed | 2026-02-20 | Low value, may replace bulk edit pages anyway |
| 91 | Data Sources - view parsed item | Merged | 2026-02-20 | → #14 |
| 92 | Link DSParsedItem to DSRawItem | Merged | 2026-02-20 | → #14 |
| 104 | Update sitemaps to include games companies | Killed | 2026-02-20 | 3k thin pages not worth it |

---

## Claude Code Workflow Opportunities

Tasks where Claude Code can help directly (no UI needed):

| Related # | Task |
|-----------|------|
| 115 | Claude-assisted game tagging (keyword matching, Nintendo data, batch suggestions) |
| 2, 18 | Mass tagging games |
| 10 | Scraping Nintendo page data (players/features done via #95) |
| 59 | Review import tool (scrape to JSON) |
| 63 | Bulk affiliate link updates |
| 69 | Finding correct Digitally Downloaded URLs |
| 70, 110 | Crawl queue / backfill operations |
