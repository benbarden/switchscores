# 105: Review provenance - how a review was created, and by whom

**Status:** Not started. Scoped 2026-07-19.
**Supersedes the original framing of #105** ("Record user id of submitted review links"), which
understated it and is partly obsolete - see "Corrections" below.

## What this actually is

Every review should record **how it was created** (source) and **who created it** (user, where a
human did it). That applies to both `review_drafts` and `review_links`, and both need to be
populated going forward and backfilled where the data can be reliably recovered.

## Verified current state (localdev, prod copy, 2026-07-19)

### `review_drafts`
- `source` column exists (`Feed` / `Manual` / `Scraper` via `ReviewDraft::SOURCE_*`).
- **It is written but never read.** Only `ReviewDraft\Director` touches it; nothing in `app/` or
  `resources/` consumes it. So today it carries no weight - and correcting it is zero-risk.
- **Reviewer self-submissions are mislabelled as `Feed`.**
  `Members/Reviewers/ReviewDraftController.php:198` calls `buildNewFeed($params)`.
  `ReviewDraft\Director::buildNewManual()` exists and is **dead code**.
- Manual submissions are identifiable by signature: synthetic title `Review of {game}` plus a
  `game_id` already set. 1,523 such drafts across sites 2647, 2648, 2652 (100% of their drafts),
  plus 340 on site 2646 and small numbers on 2099 / 2645.

### `review_links`
- **`user_id` already exists** - no migration needed for it. 456 of 38,631 populated.
- `review_type` already carries source-ish information: `Imported` 35,188 / `Manual` 3,318 /
  `Partner` 125 (`ReviewLink::TYPE_*`).
- Two creation paths:
  - `ReviewDraft\ConvertToReviewLink` - passes the draft's `user_id` through to
    `buildNewImported()`. This is why the only populated `user_id`s are on `Imported` rows.
  - `Staff/Reviews/ReviewLinkController.php:149` - `repoReviewLink->create(...)` with
    `TYPE_MANUAL`. **Takes no `user_id` at all.** This is the main gap.
- **`review_type` is effectively binary for new rows.** `ReviewLinkController.php:149` hardcodes
  `TYPE_MANUAL`, and `ReviewLink\Director::buildNewPartner()` is **never called** - dead code, like
  `ReviewDraft\Director::buildNewManual()`. There is no code path that can produce a `Partner` row.
  The 125 Partner rows are a frozen legacy set: 2017-06-17 to 2019-12-21, five small sites
  (The New Odyssey 50, Switch Indie Fix 37, The Nintendo Nomad 33, Side Quest VGM 4,
  100 Hour Reviews 1). Manual is still in use (last 2025-06-15, 19 sites).

## Corrections to earlier assumptions

1. **"They should all come via ReviewDraft anyway."** Not true for the rows that matter most:
   `Manual` (3,318) and `Partner` (125) links have **zero** linked drafts. They are created
   directly by the staff screen, bypassing drafts entirely. Only `Imported` links have drafts
   (11,838 of 35,188; the other 23,350 predate the draft model, as expected).
2. **"Add migration" for review link user_id.** Already there. What's missing is population, plus
   a decision on the source field (below).
3. **Don't blindly add a `source` column to `review_links`.** `review_type` already encodes
   provenance, but in a *different vocabulary* to drafts:
   `Feed / Manual / Scraper` vs `Imported / Manual / Partner`. Reconcile these deliberately -
   either map one onto the other, or state clearly that `review_type` answers "how it got here"
   and leave it as the source field. Adding a second overlapping column would make it worse.

## Backfill plan

**`review_drafts.source`** - flip the 1,523+ manual-signature drafts from `Feed` to `Manual`, and
fix `ReviewDraftController.php:198` to call `buildNewManual()`. Identify by the
`item_title LIKE 'Review of %'` signature rather than by site: **site 2646 is a hybrid** (507 feed
drafts *and* 340 manual ones), so this is a per-draft property, not a per-site one. Do not lean on
`review_sites.review_import_method` for it.

**`review_drafts.user_id`** - one user per review site holds in the data
(2646 → 206, 2648 → 216, 2652 → 241; each exactly one distinct user). But prefer
**`users.partner_id` → `review_sites.id`** as the key rather than copying from sibling drafts:
it is authoritative, and it also covers sites whose drafts have no `user_id` at all
(2099 → 147, 2645 → 201).
Exception: **site 2647 (Switch Weekly) has no user with `partner_id = 2647`** - its 23 drafts
cannot be resolved by either route and should stay null. Site last active 2022.

**`review_links.user_id`** - scope deliberately limited (decided 2026-07-19).
- `Imported` with a linked draft: inherit from the draft (after the draft backfill above, so it
  picks up the recovered values). **This is where the value is:** `review_type` cannot tell a
  reviewer-submitted review from a feed-imported one - both are `Imported`, because reviewer
  submissions flow through drafts into `buildNewImported()`. A present `user_id` is what
  distinguishes them.
- `Imported` without a draft (23,350, pre-draft-model): not recoverable. Leave null.
- `Manual` (3,318) and `Partner` (125): **deliberately left null.** These are all staff-created by
  Ben, so the column would be a constant carrying no information. Not worth the backfill or the
  code change. Revisit only if staff ever expands beyond one person.

## Open questions

- Reconcile `review_type` vs `source` vocabulary, or keep them separate with documented meanings?
- Should `review_sites.review_import_method` gain a `Manual` value? Currently only `Feed` and
  `Scraper` exist, with one site at NULL - and sites like 2646 are genuinely hybrid, so a
  single site-level value may be the wrong shape.
- Is `Partner` worth retiring as a review type, given nothing can create one and the last row is
  from 2019? Either wire up `buildNewPartner()` or drop it and migrate the 125 rows.

## Settled

- **No `user_id` on `Manual` / `Partner` links** (2026-07-19). All staff-created by Ben, so the
  column would be a constant. `ReviewLinkController.php:149` does **not** need changing.

## Relationship to feed health (improvements: feed health rebuild)

This is a prerequisite for a trustworthy feed-health screen. Without it, sites 2647 / 2648 / 2652
look like "active feed sites with no feed link" - i.e. broken - when they are manual-submission
partners working exactly as intended. Any alert built before this lands would fire permanently on
three healthy sites.
