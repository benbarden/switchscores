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

### Milestone 2 — DigitalOcean Spaces (needs Ben / external) ⏳ TODO

- [ ] Create DO Spaces bucket (public-read) + CDN endpoint
- [ ] Generate a read-only credential for localdev (prod unmutable from localdev)
- [ ] Migrate lowest switch-2 IDs (~a dozen); populate `game_images` (`location=spaces`)
- [ ] Prove display / replace / delete / orphan detection / staff dashboard
- [ ] Prove #7/#8: localdev with zero local images still renders (reads prod Spaces),
      then a MinIO override deviates one game only, prod untouched

### Phase 1 — bulk migrate ⏳ TODO

- [ ] Confirm the low-quality image re-download job (#70) is complete first
- [ ] Idempotent, resumable script copies all `public/img/ps-*` → Spaces, populates `game_images`

### Phase 2 — cutover ⏳ TODO

- [ ] Once counts match: drop legacy fallback from resolver
- [ ] Later delete `public/img/ps-*` → reclaims ~5 GB, unlocks smaller droplet
- [ ] Move override concept / drop legacy `games.image_*` columns

## Future: Game images dashboard

The `/staff/games/image-migration` page should eventually live *under* a broader **Game
images dashboard** that also shows image stats (req #6): missing-image counts, orphan
list (bucket vs DB diff), total bucket size, and a quality view once `game_images` gains
dimension/quality columns. The migration page becomes one tab/section of that dashboard.

## Known follow-ups (not blocking Milestone 1)

- **N+1:** resolver reads the `images` relation; list/grid pages lazy-load it. Harmless now
  (zero rows), but add `with('images')` to list repositories before Phase 1 bulk migration.
- **og:image in `news-image.twig`** does `url('/') ~ boxartUrl` — fine for relative legacy
  paths, but will double-prefix once the resolver returns absolute CDN URLs. Fix at Phase 2.

## Open items

- Confirm the low-quality image re-download job (#70) is complete.
- Estimate outbound transfer vs the 1 TiB/mo CDN allowance.
- Decide whether MinIO lives in the switchscores compose only, or a shared infra localdev compose.
