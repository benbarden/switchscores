# API V1 Deprecation & Usage Tracking Plan

Status: Phase 0+1 DONE (2026-07-14). Phases 2-3 proposed. Created 2026-07-14.

## Progress

- **Phase 0+1 shipped 2026-07-14.** `/api/game/list` now returns 410 Gone;
  logging middleware records the four public V1 game endpoints. Verified on
  localdev: 410 for `/game/list`, 200s still work for the single-record
  endpoints, all logged with `api_version = V1`; non-V1 API routes (e.g.
  `find-by-title`) are correctly NOT logged.
  - New: `database/migrations/2026_07_14_000001_create_api_request_log_table.php`,
    `app/Models/ApiRequestLog.php`, `app/Http/Middleware/LogApiRequest.php`.
  - Changed: `app/Http/Kernel.php` (`log.api` alias), `routes/api.php`
    (V1 group wrapped in `log.api:V1`, `/game/list` -> 410).
  - **Scope kept lean:** logging is applied only to the V1 game endpoints via a
    route-group middleware, not the whole `api` group. The middleware takes a
    version marker parameter (`log.api:V1`) so it can be **expanded later** to
    other API routes / versions without change.
  - **Prod deploy (manual file-copy, no git):** copy the 3 new files + 2 changed
    files across, then `php artisan migrate --force` and clear route/config
    caches. No composer changes (dependency-free).

## Background

The V2 API lives under a `/v2` prefix and is token-gated (`auth:sanctum`). The
older V1 API in `routes/api.php` has no version prefix and, for the game
endpoints below, **no auth at all** - they sit outside the sanctum group, so
anyone on the internet can call them without a token.

Goal: track API usage, then phase out the heavy/legacy endpoints, highest
impact first. One known external consumer of `/api/game/list` (an ex-Patreon
supporter) has stopped and gone unresponsive; nobody else should be using it.

## The V1 game endpoints in scope

Defined in `routes/api.php` (lines 51-58), all **public / unauthenticated**,
handled by `App\Http\Controllers\Api\Game\GameController`:

| Endpoint | Method | Live payload (localdev) | Notes |
|---|---|---|---|
| `/api/game/list` | `getList()` | **2.4 MB, 17,051 games** | The heavy one. Full table, no console filter, no pagination, uncached. |
| `/api/game/{id}` | `getDetails()` | ~2.8 KB | Single game. |
| `/api/game/linkid/{id}` | `getDetailsByLinkId()` | ~2.8 KB | Single/CSV of eShop link ids. |
| `/api/game/{id}/reviews` | `getReviews()` | ~5.2 KB | Reviews for one game. |

`/api/game/list` calls `App\Domain\GameLists\Repository::getApiIdList()` - a
full-table `select` of every game, ordered by id, returned in a single
response.

## Confirmed facts (from code + live probes)

- **All four endpoints are public.** No token required.
- **Zero internal callers.** The only references to the four controller methods
  are the route definitions themselves - no Twig, JS, or command calls them.
  Safe to retire with respect to internal usage. (The sibling routes
  `findByTitle`, `getByExactTitleMatch`, `getUnlinkedDataSourceItem` are
  separate staff tooling and stay.)
- **The V2 `list-all` shares the same heavy query.** `Api\V2\GameController@getList`
  also calls `getApiIdList()` - same 17k-row scan, fewer columns per row,
  token-gated. Same DB cost, smaller payload, known consumers.
- **No API usage logging exists.** The `api` middleware group (`app/Http/Kernel.php`)
  is only `throttle:api` + `bindings`. Sanctum's `personal_access_tokens.last_used_at`
  is the only usage signal and only covers authenticated V2 calls - nothing for
  the public V1 endpoints.
- **Route-ordering gotcha.** `/game/{id}` (line 53) is a greedy catch-all. If the
  `/game/list` route is simply *deleted*, `/api/game/list` falls through to
  `getDetails("list")`. So a 410 stub route for `/game/list` **must remain
  declared before** `/game/{id}`. (The file already shows awareness of this at
  line 32: "This must be last".)

## Decisions taken

- **Log first**, then remove - satisfies the usage-tracking goal and de-risks removals.
- **410 Gone** for retired endpoints (with a JSON message pointing at V2), kept
  for a transition window, then deleted - clearer than a silent 404 for a public
  endpoint.
- **Start with `/game/list`** as the first, highest-impact slice.

Key insight: **410 responses are still logged**, so retiring `/game/list` to a
410 in the same deploy as the logging middleware gives both the fix and the
evidence trail at once - continued callers surface as repeated 410s with their
IP, no waiting window needed.

## Plan

### Phase 0 - API usage logging (build first, ship with Phase 1)

Terminable middleware `LogApiRequest` on the `api` group, recording per request:
`method`, `path`, `status_code`, `token_id` (nullable, from Sanctum), `ip`,
`duration_ms`, `created_at`. Written to a new `api_request_log` table (varchar +
index style per CLAUDE.md conventions, no DB enums). Terminable so it logs after
the response is flushed - no added latency for the caller.

Open decision: log the whole `api` group (includes internal staff tooling - more
noise, complete picture) vs. just the public game endpoints. Leaning toward
logging everything and filtering in queries.

### Phase 1 - Retire `/api/game/list` (highest impact, first slice)

Point the route at a small 410 Gone response with JSON pointing at the V2
replacement, e.g.:

```json
{ "message": "This endpoint has been retired. See /members/developers/api/methods." }
```

Route stays declared **above** `/game/{id}` to avoid the greedy-param
fall-through. `getList()` can stay in the controller for now (dead but harmless)
or be deleted.

Phase 0 + Phase 1 ship together as one deploy: minimal effort, closes a public
2.4 MB uncached endpoint, and starts the evidence trail.

### Phase 2 - Build the V2 console+month replacements

Repo already has the building blocks: `GameCalendar\Repository::getListByConsole($consoleId, $year, $month)`,
`GameLists\Repository::recentlyAdded()` / `recentlyReleased($consoleId)`.
Proposed token-gated endpoints (short, bounded lists):

- `GET /api/v2/games/by-month/{consoleId}/{year}/{month}`
- `GET /api/v2/games/recently-added/{consoleId}`
- `GET /api/v2/games/recently-updated/{consoleId}`

Then update `resources/views/members/developers/api/methods.twig` - move these
out of "Things to be added" into the live table, and mark `list-all` deprecated.

### Phase 3 - Retire the rest

Once logging confirms low/zero traffic: 410 the three single-record V1 endpoints
(`/game/{id}`, `/game/linkid/{id}`, `/game/{id}/reviews`) - cheap but redundant
with their V2 twins. Deprecate `list-all` in docs now; remove it once the
by-month endpoints are live and any active V2 token holders have migrated (check
`personal_access_tokens` for active token count first).

## Sequencing rationale

Phase 0 + 1 together kill the single 2.4 MB public endpoint immediately while
starting the usage log. Everything after is lower urgency: the single-record
endpoints are small, and `list-all` is token-gated so its blast radius is known.
