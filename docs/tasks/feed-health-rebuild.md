# Feed health rebuild (step 3)

Scoping notes. Not started.

## Do this first: `was_last_run_successful` never records a load failure

**A feed that cannot be fetched keeps its last successful status forever.** Any dashboard built
on `was_last_run_successful` inherits the lie, so fix this before building anything on top of it.

`ImportByFeed::runImport()` calls `Loader::loadByUrl()` *before* `processItems()`, and
`processItems()`'s catch block is the only place that sets `was_last_run_successful = 0` and
writes `last_run_status`. A fetch failure therefore throws straight past it, out of `runImport()`,
and is only logged by the caller:

```php
// ImportActiveFeeds::handle()
try {
    $this->importByFeed->runImport();
} catch (\Exception $e) {
    $logger->error($e->getMessage());   // logged, never persisted
}
```

**Live example - feed 31, Génération Nintendo.** Reads `was_last_run_successful: 1`,
`last_run_status: 'Imported: 0 - Skipped: 129'`, but that record is stale from before ~Nov 2025.
The site is now behind a Cloudflare JavaScript challenge: every user agent gets HTTP 403,
including a full Chrome UA, so it is not a user-agent problem and cannot be spoofed - it needs a
real browser engine. `last_review_date` stuck at 2025-11-07 was the only visible symptom, and the
feed had been dead for roughly eight months while reporting success.

Fix: record the failure against the feed link wherever it is thrown - catch around the whole of
`runImport()`, or move the status write up so it covers loading as well as processing. Then feed
health can show "last successful fetch" as a real value.

Also worth deciding here: what to do with feeds that are permanently blocked (ask the partner to
allowlist the crawler by UA or IP, or mark the feed Broken).

## Carried in from step 1 / step 2 planning (2026-07-19)

- **Match-rate trend detection.** Spotting a feed whose match rule suddenly starts failing across
  the board - i.e. the site changed its title format. Real example: Seafoam Gaming changed format
  after 2026-06-14 (`FZ: Formation Z (Switch 2) – Review` - bare `(Switch 2)` token plus an en
  dash separator, where the rule expected `(Switch 2 eShop)` and `)- Review`). The only signal was
  one skipped row in a backfill run that happened by chance. Without it, the feed would have kept
  failing unnoticed.
  - No new schema needed to start: step 1 persists `parse_status` and every draft now carries
    `feed_link_id` + `created_at`, so match rate per feed over time is a `GROUP BY` on existing
    columns. Data accumulates from the step 1 deploy onwards.
  - A dedicated run-stats table is only needed for point-in-time records of runs that produced no
    drafts at all. Decide here.

- **Multibyte characters in match rules.** `MatchRule::prepareRule()` builds `/^...$/` with **no
  `u` modifier**. A multibyte character inside a `[...]` character class therefore matches a single
  byte and silently never fires - e.g. `[-–]` looks correct and cannot match. Outside a class
  (`(?:-|–)` or a literal) it works.
  - Options: have the step 2 tester detect non-ASCII bytes inside `[...]` and warn, or add the `u`
    modifier to `prepareRule()`. The latter changes matching for all 49 existing feeds, so it needs
    its own testing - a deliberate decision, not a casual fix.

- **Manual-submission sites are not broken feeds.** Sites 2647 / 2648 / 2652 are Active with feed
  drafts but no feed link, because reviewer self-submissions are mislabelled `source = 'Feed'`.
  Any "active feed site with no feed link" alert built before #105 lands will fire permanently on
  three healthy sites. See `docs/tasks/105-review-provenance.md`.

- **`FeedHealthController::landing()` uses `firstBySite()`** - shows only the first feed for a
  multi-feed site. Needs to handle several feeds per site.

- **Parse status labelling.** Step 1 persists `Could not locate game`. Drafts matched by hand
  afterwards keep that label and appear in the partner-facing pie chart against reviews that were
  created fine. Decide presentation here - option (b) from the step 1 discussion was to add a
  `PARSE_STATUS_MANUALLY_MATCHED` set by the draft edit screens, making the chart read
  auto / manual / pending.
