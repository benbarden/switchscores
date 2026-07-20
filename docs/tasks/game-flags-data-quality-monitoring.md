# Game flags as the data quality monitoring system

Scoping notes. Not started.

Replaces the long-neglected data quality dashboard (`/staff/data-quality/dashboard`) with
automatic flagging and un-flagging on `game_flags`. The aim is **not** to generate a manual to-do
list. It is to surface issues that are **new, emerging, or growing** - problems worth knowing
about before they become visible on the site.

## Why now: the bug that nothing caught

On 2026-07-20 a stale-discount bug was found in the Nintendo eShop import. Discounts were written
when a sale started and **never cleared when it ended**, so ended sales persisted indefinitely.
At the point of discovery, **4,059 of 5,233 stored discounts (~78%) were stale** - the live site
was showing thousands of sale badges and struck-through prices for games the eShop sold at full
price.

It had presumably been that way for a long time. Nothing surfaced it. Not one of the seven
`integrity_checks` looks at prices, and nothing anywhere watches for a value that has stopped
changing. It was found only because a single flagged game was inspected by hand.

That is the gap this task exists to close: **the site can be substantially wrong in a way that
looks entirely plausible from the outside.**

## Why `game_flags` rather than `integrity_checks`

The two are not really rivals, and it is worth being precise about the difference:

| | holds | answers |
|---|---|---|
| `integrity_checks` | `check_name`, `is_passing`, `failing_count` | "15 games have no release year" |
| `game_flags` | `game_id`, `flag`, `notes` | "*this* game has no release year" |

**Counts are derivable from rows; rows are not derivable from counts.** So flags should be
primary - the aggregate comes for free, and the detail is retained.

### But three existing checks are not about games

`integrity_checks` currently holds seven checks, and three sit on other entities:

- `TitleHashNoGameMatch` - `App\GameTitleHash`
- `ReviewLinkDuplicate` - `App\ReviewLink`
- (`GameNoTitleHashes` / `GameTitleHashMismatch` are game-rooted but hash-related)

`game_flags` has nowhere to put those. Two options, decide before building:

1. Keep a thin integrity-check surface for non-game entities, or
2. Generalise to `entity_flags` (`entity_type` + `entity_id`), with `game_flags` as a view over it

Do **not** force non-game checks into `game_flags`.

## The hard problem is un-flagging

This will make or break the design.

There are currently 203 games flagged `price-check-deluxe`. The 2026-07-20 investigation
established that a meaningful share of them are **not fixable from the API data at all** - the
Nintendo record only ever carries the upgrade-pack price and the true RRP appears nowhere (see
game 17832, Isekai Villain Nintendo Switch 2 Edition).

If an automated job re-evaluates and re-flags, **those games return on every run, forever**. The
queue never empties, the number stops meaning anything, and the page gets ignored. That is
precisely how the data quality dashboard died: `GameNoReleaseYear` has read 15 since 2026-05-05
and communicates nothing.

**Without a way to say "I have looked at this and it stays", automated flagging is worse than
manual flagging.**

### Required states

Three, not two:

1. **Open** - auto-flagged, not yet reviewed
2. **Accepted** - reviewed; cannot or should not be fixed. Suppresses re-flagging. Records a
   reason. This is the state that keeps the queue meaningful.
3. **Resolved** - condition no longer true; auto-unflagged

### Auto must not clobber manual

Flags are currently applied and removed by hand. A job that clears flags indiscriminately will
wipe in-progress review state and lose Ben's place in a list of 203.

Add a `source` column (`auto` / `manual`). **Jobs may only ever touch rows they created.**
A manually applied flag is never auto-removed.

## History is the actual feature

Neither system can currently answer "is this getting worse?", and that is the thing worth having.

`integrity_checks.failing_count` is a single number that overwrites itself. You can see it is 15.
You cannot see it was 3 last month. Same for a flag count - a bare total is a to-do list, not
monitoring.

**One small table: flag name, date, count. Written once per run.** That is what enables:

- "price-check-deluxe: 203, up from 180 last week" (**growing**)
- "a flag type first appeared 3 days ago" (**new**)
- "this has been flat at 15 for two months" (**stale, probably accepted-in-practice**)

This table matters more than the flagging mechanism itself. Flags without history reproduce the
dashboard that already got neglected, with better styling.

## Candidate checks

Starting set, in rough order of value:

1. **`price_eshop = price_eshop_discounted`** - a discount that isn't a discount. Currently
   returns 23 (all Switch 2 games mid-sale whose stored standard price is the sale price). Cheap,
   precise, and directly derived from the 2026-07-20 investigation.
2. **Stale value detection** - a field that has not changed in N days while the source data says
   it should have. This is the class the discount bug fell into and the class nothing currently
   watches. Hardest to design, highest value.
3. **`api-no-price`** - the Nintendo API has no usable price for a game we *do* hold a price for.
   Currently 40 games (all Switch 2, parsed `price_standard = 0.00` against a real stored price).

   These were, until 2026-07-20, the **entire** contents of the price differences report:
   `Differences::getPrice()` was a bare `g.price_eshop != dsp.price_standard`, with no knowledge
   of the guards in `UpdateGame::updatePrice()`. So it reported 40 differences the importer had
   deliberately decided not to apply, and **zero** real ones - an alert that is 100% noise, which
   is how you learn to ignore the alert. The query now mirrors those guards and reports 0.

   But the information is real and shouldn't be lost: "the API has no price for this game" is
   worth knowing, it just isn't a difference to *apply*. It belongs in a flag, auto-applied and
   auto-cleared when the API supplies a price again.

   **Structural point worth keeping in mind:** `DSNintendoCoUkUpdateGames` runs nightly and
   applies every difference it can, so any *applicable* difference is transient by definition. A
   correctly-filtered differences report will sit near zero almost always. The lasting value was
   never "differences pending" - it was the anomalies, which are exactly what flags should hold.

4. The four game-level checks migrated from `integrity_checks`.
5. **`data_source_parsed` rows with `link_id IS NULL`** - 6,654 orphans, all created
   2021-11-15, still fed to `DSNintendoCoUkUpdateGames` (8,650 rows processed). Rows are ordered
   by `game_id` only, so where a game has both a real and an orphan row **the winner is
   arbitrary** and 2021 values can overwrite current data. Not a game flag as such, but it needs
   to be visible somewhere.

## Retire

- **Duplicate reviews** (`/staff/data-quality/duplicate-reviews`) - currently empty
- **Category dashboard** (`/staff/data-quality/category/dashboard`) - superseded by the
  Categorisation dashboard

## Build order

1. `source` column on `game_flags` (`auto` / `manual`) - unblocks everything else safely
2. `accepted` state + reason - without it, auto-flagging is a downgrade on today
3. History/snapshot table - the step that turns a list into monitoring
4. Port checks one at a time, starting with `price = discounted`
5. Dashboard last

**Do not build the dashboard first.** A dashboard over flags that cannot be accepted or trended
is a prettier version of the page that already got neglected. The state model and the history
table are what make it stick.
