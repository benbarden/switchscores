# #163 - Staff dashboard

Plan agreed and approved by Ben 2026-09-27 (decisions 6-10). Build order: the feed-health fix,
then Phase 1, then Phase 2. **Feed-health fix done on localdev 2026-09-27, deploy pending** (see
`feed-health-rebuild.md`); the Partner feeds tile should read `last_successful_run_at`, which only
fills in after the first nightly run following deploy.

## Goal

Turn the staff home (`resources/views/staff/index.twig`, `Staff/IndexController`) into a dashboard
that says what needs doing now and whether the site is growing, in the style of the Product Notes and
Switch Scores search pages Ben uses in claude-context: large numbers, amber/red when something has
waited too long, and charts with one clear reading.

Two parts, combined on one page (Ben's call, 2026-09-27): **needs attention** (queues and health) at
the top, **growth** (content over time) below it. The existing link tables stay, lower down.

---

## Decisions log

| # | Decision |
|---|----------|
| 1 | One page: the staff home, not a separate Dashboard page. The dashboard band goes at the top; the existing link tables stay below so nothing in daily use moves. |
| 2 | The crawl and recently-added columns are condensed to 3-5 items each, made smaller, and moved down the page. Neither is compelling at full size. |
| 3 | Removed from the staff home: "Invite code requests" (archived, not deleted - route and page stay, just unlinked from here), "Import games from JSON", "No category with collection", "Duplicate reviews". |
| 4 | Tiles respect the same roles as the rows they replace (Reviews manager, Games manager, Data source manager, owner). |
| 5 | Charts use the existing Chart.js 2.8 (`/js/chartjs/Chart.min.js`), already used by `staff/insights/page.twig` and `staff/stats/reviewLink.twig`. No new library. |
| 6 | Phase 1 thresholds confirmed as proposed in the table below (Ben, 2026-09-27). |
| 7 | Feed health: **option A** - do the `was_last_run_successful` fix from `feed-health-rebuild.md` first, then build the Partner feeds tile on the flag (Ben, 2026-09-27). |
| 8 | Growth charts use **monthly** buckets over 12 months - the calmer view for a page opened every session. Claude's default; Ben had no preference. Easy to switch. |
| 9 | The crawl column only shows one of its three lists at a time, so condensing it is a matter of item count and size, not choosing between lists. |
| 10 | Plan approved (Ben, 2026-09-27). Build order: feed-health fix, then Phase 1, then Phase 2. |

---

## Page layout (top to bottom)

1. **Needs attention** - a row of tiles (Phase 1).
2. **Growth** - charts over 12 months (Phase 2).
3. **Link tables** - the current Submissions / Games to add / Nintendo links / Missing data / Registration
   tables, minus the removed links (decision 3).
4. **Crawl and recently added** - condensed (decision 2): Crawl priority queue, Next to crawl and
   Recently added each cut to 3-5 items, smaller packshots, each with its existing "view all" link.

---

## Phase 1 - Needs attention

Each tile: a large count, a label, one line of context (usually the age of the oldest item), and a
state chip. State is also carried by the chip text, never colour alone.

| Tile | Source | State rule (starting defaults - Ben to confirm) |
|---|---|---|
| Review drafts | `ReviewDraft` unprocessed (`repoReviewDraft->countUnprocessed()` + oldest `created_at`) | amber if oldest > 3 days, red > 7 |
| Quick reviews | pending quick reviews (existing binding) | amber if any > 3 days, red > 7 |
| Featured games | pending featured games (existing binding) | amber if any > 7 days |
| Contact submissions | new contact submissions (existing binding) | amber if any > 3 days |
| Games company signups | existing binding | amber if any unactioned > 7 days |
| Nintendo.co.uk unlinked | existing binding | count only, no state |
| Partner feeds | `PartnerFeedLink` - see dependency below | red if any feed failing; amber if any `title_match_rate` below a threshold |
| Scheduled jobs | `JobRun` (latest per `command`: `status`, `finished_at`) | red if the latest run failed, or a daily job has no run in 26 hours |
| Integrity checks | `IntegrityCheckResult` latest per check (`is_passing`, `failing_count`) | amber if any failing |

### Dependency: feed health

`docs/tasks/feed-health-rebuild.md` records that **`was_last_run_successful` never records a load
failure** - a feed that can't be fetched keeps its last successful status forever. So the Partner
feeds tile must not be built on that flag until the fix in that doc lands. Options:

- **A.** Do the feed-health fix first (it's small and self-contained), then build the tile on the flag.
- **B.** Build the tile now on a different signal - days since the last review draft from each active
  feed - and switch to the flag after the fix.

**Decided: A** (decision 7).

### Build notes

- Bindings added in `Staff/IndexController` via existing repositories; add repository methods for
  "oldest unprocessed" where they don't exist.
- A shared Twig macro in `ui/components/` for the tile and state chip, reusable on other staff pages.
  Styling via custom properties in `staff-b5/custom.css` (same place #160 plans to put shared staff
  form CSS).
- State thresholds in one place (a small config array or class), not scattered through Twig.
- Tests: unit tests for the state rules (boundary days), a feature test that the staff home renders
  for each role and hides tiles the role can't see. Fast suite in the container.

---

## Phase 2 - Growth

Charts over the last 12 months, weekly or monthly:

- **Games added** - `games.created_at`.
- **Reviews linked** - `review_links.created_at`.
- **Score coverage** - share of released games with enough reviews to be ranked. Needs confirming
  which field or rule defines "ranked" before building.

Aggregate queries cached (e.g. one hour) so the staff home stays fast. One series per chart, no
dual axes.

---

## Later, optional - Search

`GscPageSnapshot` already holds page-level GSC data in the app. A search panel could use it for live
figures instead of the exports read by the claude-context search page. Scope separately.

---

## Open questions for Ben

None outstanding - answered 2026-09-27 (decisions 6-9).
