# Packshots → object storage (DigitalOcean Spaces) migration

## Summary

Move header + square packshots (~5 GB in `public/img/ps-header`, `public/img/ps-square`)
off the server disk into DigitalOcean Spaces (S3-compatible), so the app is no longer
tied to server disk. Unblocks a smaller droplet and the switchscores server migration.

**Full proposal (decisions, rationale, architecture):**
`~/Documents/claude-context/side-projects/switch-scores/images-migration-proposal.md`

## Key decisions (see proposal for full rationale)

- **Chokepoint:** all display URLs resolve through one PHP service (`ImageResolver`),
  registered as the `packshot_url()` Twig function. `resolve.twig` + `ImageHelper`
  delegate to it, so display logic lives in testable PHP, not Twig.
- **Schema:** dedicated 1:1 `game_images` table (keeps growing image metadata off the
  hot `games` table). `location` = `legacy` | `spaces` (varchar+index, not enum, per
  project style). Only `image_square` / `image_header` move here; the eShop ingestion
  overrides (`packshot_square_url_override`, `nintendo_store_url_override`) stay on `games`.
- **Storage:** env-driven `packshots` disk (s3 driver). Prod → Spaces + CDN.
  Localdev → MinIO container, with `PACKSHOTS_URL` pointing at the prod Spaces CDN so
  localdev links to prod images (no 5 GB download); MinIO holds only per-game overrides.
- **Bucket layout:** one bucket, keys prefixed `{console}/{type}/{gameId}-{slug}.ext`
  (e.g. `switch-2/square/…`). DB is source of truth for the current filename.
- **Access:** public-read bucket + CDN (marketing images; no signed URLs).
- **POC batch:** lowest game IDs for switch-2.
- **Fallback:** resolver reads `game_images` when a row has `location = spaces`, else falls
  back to legacy `games.image_*` + `public/img`. Migrated + un-migrated games coexist
  through the POC. Legacy columns/path dropped only at Phase 2 cutover.

## Progress

### Milestone 1 — code foundation (site renders identically) ✅ DONE (verified 2026-07-13)

No `game_images` rows exist, so every lookup falls through to legacy `/img/ps-*`.

- [x] Migration `2026_07_13_000001_create_game_images_table.php` (varchar+index for `location`)
- [x] `App\Models\GameImage` (`LOCATION_LEGACY`/`LOCATION_SPACES`) + `Game::images()` hasOne
- [x] `App\Domain\Game\ImageResolver` — single source of truth (`url($game, $type)`).
      NB: lives in `App\Domain\`, not `App\Services\` (deprecated — coding-standards.md)
- [x] `packshot_url()` Twig function in `TwigViewServiceProvider`; `resolve.twig` rewired;
      `ImageHelper` + `Api/V2/User/CollectionController` delegate to the resolver
- [x] Env-driven `packshots` disk in `config/filesystems.php` (`PACKSHOTS_*`, throw=false)
- [x] MinIO + `minio-createbucket` services in `docker-compose.yml` (`minio_data` volume)
- [x] `PACKSHOTS_*` keys documented in `.env.example` (MinIO localdev defaults, commented)
- [x] **Verified:** migration applied in VM, localdev site loads, packshots render via legacy

Incidental fixes flagged (only affect previously-broken image-less cases):
- `resolve.twig` used to emit literal `''` for image-less games → now empty → placeholder shows
- `ImageHelper::packshotHtml` emitted `<img src="{filename}">` without the path prefix → now full URL

### Milestone 1b — staff-driven migrate/revert (no external account) ✅ DONE (verified 2026-07-13)

Rather than a synthetic test file, we built real reversible tooling. The full legacy
image set IS present on this dev box (`public/img/ps-*`, ~17k/18k files), so migrate
copies the game's genuine bytes into MinIO. Decisions: strip `sq-`/`hdr-` prefix →
`{gameId}-{slug}.ext`; revert deletes the bucket object (location flips back to legacy;
legacy disk files never touched, so lossless).

Built:
- [x] `ImageResolver::storageKey()` extracted (shared read/write key layout)
- [x] `App\Domain\Game\ImageStorageMigrator` — `migrate($game)` / `revert($game)`
- [x] `Repository::getByConsoleLowestIdsWithImages()` (eager-loads `images`, no N+1)
- [x] `Staff\Games\ImageMigrationController` + routes `staff.games.image-migration.*`
- [x] Staff page `staff/games/image-migration.twig` — lowest 20 switch-2 games,
      location badge, live thumbnail, Move to Spaces / Move back buttons

Verified:
- [x] Install S3 adapter: `composer require league/flysystem-aws-s3-v3 "^3.0"`
      (Laravel doesn't bundle it; also needed on prod for real Spaces)
- [x] MinIO up + `PACKSHOTS_*` set + `config:clear`
- [x] Visit `/staff/games/image-migration`, Move one game to Spaces →
      thumbnail + `<img src>` point at MinIO; others stay legacy; object in bucket;
      game_images row shows `location=spaces`
- [x] Move back → object deleted from bucket, thumbnail returns to legacy path

**Gotcha found & fixed:** host port 9000 clashes with PhpStorm/Xdebug (request just
spins). MinIO S3 API moved to host 9100 (`"9100:9000"`); `PACKSHOTS_URL` uses `:9100`.
Documented in `infra-red/docs/minio.md`.

### Milestone 2 — DigitalOcean Spaces (needs Ben / external) ⏳ IN PROGRESS

- [x] Create DO Spaces bucket + CDN. Bucket `switchscores-packshots`, region London (`lon1`),
      Standard storage, CDN enabled. $5/mo (account base).
- [x] Create a **limited** access key scoped to this bucket (read/write/delete only — NOT
      full account access, which could create/delete other buckets). Lives in prod `.env`.
- [ ] Set prod `.env` and `config:clear`. Confirmed non-secret values:
      ```
      PACKSHOTS_REGION=lon1
      PACKSHOTS_BUCKET=switchscores-packshots
      PACKSHOTS_ENDPOINT=https://lon1.digitaloceanspaces.com        # region host, NO bucket
      PACKSHOTS_URL=https://switchscores-packshots.lon1.cdn.digitaloceanspaces.com  # CDN, bucket in host, no path suffix
      PACKSHOTS_USE_PATH_STYLE=false                                # Spaces = virtual-hosted (MinIO = path-style/true)
      ```
      Gotcha: DO shows the origin endpoint *with* the bucket subdomain; strip it — with
      `use_path_style=false` the SDK adds the bucket from `PACKSHOTS_BUCKET`, else it doubles.
- [ ] Small-scale test: migrate lowest switch-2 IDs via the staff page; confirm CDN serves them
- [ ] Later: read-only credential for localdev (when localdev reads prod Spaces) — not needed
      yet (localdev still runs entirely against MinIO)
- [ ] Prove display / replace / delete / orphan detection / staff dashboard
- [ ] Prove #7/#8: localdev with zero local images still renders (reads prod Spaces),
      then a MinIO override deviates one game only, prod untouched

### Phase 1 — bulk migrate ⏳ TODO

- [x] Confirm the low-quality image re-download job (#70) is complete first — **done 2026-07-15.**
      It rode along with the full game-page scrape (dead-link check), completed Mar/Apr 2026.
      NOTE: filename dates are NOT a completeness signal. The re-download only renamed files it
      actually replaced; games whose saved image already matched the remote Content-Length were
      left alone and kept their old undated name. So undated = "didn't need re-downloading", not
      "not processed" — a filename-pattern query can't distinguish the two and will mislead.
- [ ] Idempotent, resumable script copies all `public/img/ps-*` → Spaces, populates `game_images`

### Ingestion repoint + default location (required before server move) ✅ BUILT 2026-07-20 (not yet flipped)

New-game ingestion wrote images to local `public/img/ps-*` and created **no** `game_images` row,
so new games displayed via the legacy fallback. Activating `PACKSHOTS_*` did not change that: the
only code writing to the `packshots` disk was `ImageStorageMigrator` (the staff button).

- [x] `config/packshots.php` + `PACKSHOTS_DEFAULT_LOCATION=legacy|spaces`, defaulting to `legacy`.
- [x] **`App\Domain\Game\PackshotWriter`** — the single write seam. Ingestion downloads to
      `storage/tmp` and hands the temp file over; the writer places it (`legacy` → move to
      `public/img`, set `games.image_*`, no row; `spaces` → `put()` to the disk + upsert the
      `game_images` row) and owns persistence. Same one-shared-helper shape as `PackshotJoin`.
- [x] Delete path made storage-aware (`Services/Game/Images.php`, req #5).
- [ ] **Flip to `spaces`** — a prod `.env` change, safe now the backfill is at 100%.

**Five write paths, not one.** The doc previously named only `Services/DataSources/NintendoCoUk/Images.php`
and the crawl commands. The full set, all now routed through the writer:
`DownloadByOverrideUrl` (the live scraper path), `GameImport\SquareImageDownloader`,
`GameImport\HeaderImageScraper`, `GameCrawlBatch::downloadHeaderImage`,
`GameCrawlUrl::downloadHeaderImage`, plus the dormant `DownloadByDataSource` /
`DownloadImageFactory`. The two crawl commands never used the Images service at all — they built
filenames and `file_put_contents()`ed to `public/img` directly, so they would have kept writing
locally however carefully the service was repointed.

**Callers must no longer set `games.image_*` themselves.** The writer owns persistence. Under
`spaces` a caller assignment would repopulate the legacy column and point the resolver's fallback
at a file that was never written locally — a broken image appearing only when the object storage
lookup misses. Removed from all five paths.

**`targetFilename()` moved to `ImageResolver`** (was private on `ImageStorageMigrator`), so
ingestion and backfill derive the same object name. If they diverged, re-downloading a migrated
game would write a second object beside the first and leak the original.

**`*_updated_at` is load-bearing, not bookkeeping.** A re-download keeps the same filename, so the
object URL is unchanged and Cloudflare would serve the old image forever. `spacesUrl()` appends
`?v={updated_at}`, so bumping it is the only thing that makes a replaced packshot appear — which
is the entire point of the override-URL flow.

**Per-type upsert.** `game_images` has one row per game, and the backfill writes both filenames
together because it always has both legacy files in hand. Ingestion does not — it can fetch a
header without a square — so the writer updates one type at a time. Writing both would null the
packshot that wasn't downloaded.

#### The trap: `isEligibleForDownload()` was legacy-only

Not in the original checklist, and the thing that would actually have bitten. `DownloadPackshotHelper::isEligibleForDownload()`
decided "does this game still need packshots?" from `games.image_*` plus `file_exists()` under
`public/img`. **Both are legacy-only signals.** A game in object storage has null columns and no
local file, so from the moment `PACKSHOTS_DEFAULT_LOCATION=spaces` every such game looks
permanently eligible: each run re-scrapes Nintendo and re-uploads identical images, indefinitely,
with no error to notice.

Now asked through `ImageResolver::url()`, which answers for whichever location the game is
actually in. The on-disk check is retained for legacy games only (the column can name a file
that isn't there — how the missing-header games were found); spaces games are trusted from the
row rather than paying a HEAD per game per run.

Covered by `tests/Unit/Domain/DataSource/PackshotEligibilityTest.php`, **verified failing against
the old logic first**.

#### Verified on localdev (MinIO)

Fast suite 397 → 411. End-to-end against the real MinIO container (not `Storage::fake`):
object lands at `switch-1/header/{id}-{slug}.jpg`, bytes match, temp file cleaned up, row records
`location=spaces` with `header_updated_at`, legacy column untouched, resolved URL carries `?v=`,
and the game is not re-eligible for download.

### Phase 2 — cutover ⏳ TODO

- [x] ~~**BLOCKER: raw-row list pages can never resolve to object storage.**~~ **FIXED 2026-07-20.**
      `ImageResolver::gameImage()` returned null for anything that wasn't an Eloquent `Game`, so any
      list built with `DB::table('games')` fell through to `legacyUrl()` unconditionally — no matter
      what `game_images` said. Harmless while legacy files exist; broken images the moment ingestion
      writes only to object storage, or the legacy delete below runs.

      **Fix: `App\Domain\Game\PackshotJoin`.** `apply($query)` left-joins `game_images` and selects
      its columns under `packshot_*` aliases; `hydrate($row)` rebuilds a `GameImage` from them via
      `newFromBuilder()` (so the datetime casts survive — `spacesUrl()` calls `->timestamp` on them).
      `ImageResolver::gameImage()` now falls back to `PackshotJoin::hydrate()` for raw rows. One
      shared helper rather than five patched queries, so the read and write sides cannot drift.
      Aliases are prefixed because `Tag\Repository` selects `games.id AS game_id`, which a bare
      `game_id` alias would have silently overwritten. `game_images` is unique on `game_id`, so the
      left join cannot duplicate rows (verified: tag 123 returns 783 of 789 candidates, filtered not
      inflated).

      **The audit in this doc was wrong in two places — corrected:**
      - `getByTagWithDates()` is listed as a risk but **has no callers at all** (dead code).
      - The live tag-page risk is `Tag\Repository::rankedByTagMerged()` and `hiddenGemsByTagMerged()`,
        which were **not listed**.
      - `onSaleHighestDiscounts()` **is** affected: `list-games-on-sale.twig` includes
        `on-sale/table-ranked.twig` three times, once per tab, and that partial renders packshots.
        (Briefly mis-assessed as image-free by grepping the wrapper view instead of following its
        includes — the wrapper has no image markup of its own.)

      **Five live methods, all now joined:** `GameLists\DbQueries::onSaleHighestDiscounts()`,
      `onSaleGoodRanks()`, `onSaleUnranked()`; `Tag\Repository::rankedByTagMerged()`,
      `hiddenGemsByTagMerged()`. `GameLists\Repository::getAll()` (deprecated) and `getApiIdList()`
      (API, no packshots) remain fine. Tests: `tests/Unit/Domain/Game/PackshotJoinTest.php` (7),
      verified failing against the old resolver first. Re-audit before cutover with
      `grep -rn "DB::table('games')" app/`.
- [ ] Once counts match: drop legacy fallback from resolver
- [ ] **Delete legacy images ONE BY ONE, verified — do NOT bulk-wipe `public/img/ps-*`** (decided 2026-07-15).
      Reclaims the same ~5 GB, just slower. Rationale: a bulk delete assumes `game_images` is a complete
      and accurate account of what's on disk, and we already know it isn't — see the missing-header games
      below. Anything the DB doesn't know about would be destroyed silently, with no way to tell afterwards
      whether it mattered.
      Method: for each game with `location = spaces`, confirm the object actually exists in Spaces, then
      delete that game's local file. **Whatever remains in `public/img/ps-*` afterwards is by definition an
      orphan** — a file no game row points at. Expect some: images are deleted with their game, but leftovers
      are likely from earlier eras. Review the remainder before deleting; some may match the missing-header
      games (i.e. the file exists under a name the DB no longer references), which would make them a fix
      rather than a deletion.
- [ ] **Games with genuinely missing header images** — decide what to do (2026-07-15: at least one known).
      Surfaced by the migrate guard, which refuses them and reports the id; they resurface at the head of
      every batch until resolved. Two options per game: re-crawl to fetch the image, or null the
      `games.image_header` column so the DB stops naming a file that doesn't exist (a game with no packshot
      is legitimate and migrates cleanly). Handle once the bulk run is otherwise clear.
- [ ] Move override concept / drop legacy `games.image_*` columns

## Game images dashboard + migration tool ✅ DONE (2026-07-13)

**Dashboard** `/staff/games/images` (`staff.games.images.dashboard`), two sections:
- **Game image stats:** games with images, games without images, orphaned images
  (deferred — shows "scan pending"; needs a bucket+disk listing, req #5).
- **Migration to CDN:** big tiles = in legacy / in Spaces / % migrated, an overall progress
  bar, and per-console breakdown bars.

**Migration tool** — two separate subpages, each reached by its own dashboard button:
- **To be migrated** `/staff/games/images/migration` (`images.migration.show`): unmigrated games
  (have images, no `spaces` row), oldest-id first, console filter, 50/page paginated. Per-row
  "Move to Spaces" + a **"Migrate next 50"** batch button (`migrateBatch`, respects the filter).
- **Recently migrated** `/staff/games/images/migration/recent` (`images.migration.recent`):
  `spaces` games newest first, paginated 50/page, each with a "Move back" (revert) button.

Stats + list queries in `App\Domain\Game\Repository\GameImageRepository`.

**Notes / follow-ups:**
- `migrateBatch` runs synchronously (50 games × 2 uploads per request). If it times out on
  prod, move it to a queued job (the progress bar already gives the readout to watch).
- Orphaned-images tile + total bucket size are the remaining req #6 dashboard pieces.
- Quality view once `game_images` gains dimension/quality columns.
- Possible follow-up: cache the count queries (full-ish scans of `games`) if the page is slow.

## Known follow-ups (not blocking Milestone 1)

- ~~**N+1:** resolver reads the `images` relation; list/grid pages lazy-load it.~~ **Done 2026-07-15:**
  `with('images')` added to the 23 display-facing methods in `GameLists\Repository`. Deliberately NOT
  added to maintenance queries (crawl queues, no-price/no-tag/no-category triage, eshop crosschecks)
  or to `allGames()`, which loads all ~17k games and would pull 17k image rows with them. New display
  list methods need it adding by hand — the eager-load can't be enforced from the resolver end.
  This only covers Eloquent queries; see the raw-row blocker under Phase 2.
- ~~**og:image in `news-image.twig`** does `url('/') ~ boxartUrl` — fine for relative legacy
  paths, but will double-prefix once the resolver returns absolute CDN URLs. Fix at Phase 2.~~
  **Fixed 2026-07-15 — and "Phase 2" was the wrong call.** This did not wait for the cutover: it
  broke as soon as the *first* game was migrated, and was live on prod emitting
  `content="https://www.switchscores.comhttps://switchscores-packshots..."` on every migrated
  game page — i.e. broken social share previews. Now prefixes only when the URL is relative
  (`boxartUrl starts with 'http'`). **Lesson: anything that assumes the resolver returns a
  relative path breaks at first migration, not at cutover.**
  **There are TWO og:image emitters, and the doc previously named only one:**
  `theme/wos/base.twig:53` (the `GameData` branch — this is the one game pages use, and the one
  that was live-broken) and `ui/components/news/news-image.twig` (the `NewsItem` branch). Both
  fixed. Full audit done: those are the only two, and nothing wraps a packshot URL in `asset()`.

## Open items

- ~~Confirm the low-quality image re-download job (#70) is complete.~~ Confirmed 2026-07-15 — see Phase 1.
- Estimate outbound transfer vs the 1 TiB/mo CDN allowance.
- Decide whether MinIO lives in the switchscores compose only, or a shared infra localdev compose.
