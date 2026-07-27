# Feed onboarding tool: probe a URL, prefill the records

**Status: BUILT 2026-07-27. All four steps done, verified on localdev, not yet deployed.**

Entry point: **Feed links -> "Add from feed URL"** (`/staff/reviews/feed-links/probe`).
The old manual add form is still there, as "Add a feed link manually".

A staff-side screen that takes a feed URL, fetches it once, works out as much of the
`ReviewSite` + `PartnerFeedLink` configuration as it can, and offers it back as a prefilled,
editable form. **Not** partner-facing self-serve, which was considered and rejected on
2026-07-06 for good reasons that still hold.

## Why now

Review site onboarding happens a few times a year. That is precisely the problem: it is rare
enough that the forms are never fresh in mind, and rare enough that "just do this one by hand and
build the tool later" always wins on the day. The live request is the only thing that ever
motivates the build, so spending it on a manual workaround also spends the motivation. The same
argument then returns unchanged at the next request.

Casualvania (July 2026) is the trigger case and the first test.

## Where the current flow is actually slow

Three screens in sequence:

1. `/staff/reviews/review-sites/add` - `name`, `website_url`, `link_title`, `rating_scale`,
   review import method. All typed by hand.
2. `/staff/reviews/feed-links/add` - site, `feed_url`, then **`data_type` (Array/Object) and
   `item_node` (channel>item / post / item / entry) chosen blind from dropdowns**, plus
   `title_match_rule_pattern` / `_index`, `feed_url_prefix`, `allow_historic_content`,
   `feed_status`.
3. `/staff/reviews/feed-links/test-title-rule/{linkId}` - the screen that actually shows what the
   feed contains. **It only exists once the link has been saved.**

The friction is specific, not general clunkiness: **the two fields most likely to be wrong must
be committed before anything can validate them.** `data_type` in particular is a rule nobody
holds in their head - and, as it turned out, a rule that had been written down wrongly (see
"The parse mode rule was wrong" below). Getting it wrong fails quietly rather than loudly.

Quiet failure is the recurring theme in this area:

- `ParseTitle` bailed on any multi-feed site for 8 months. Reviews still appeared, because they
  were being matched by hand, so the bug moved work to a human instead of visibly failing.
- Génération Nintendo read `was_last_run_successful = 1` while dead for ~8 months.
- Seven feeds were found with silently broken title rules, none suspected.

## What is auto-detectable from one fetch

| Target field | How it is derived |
|---|---|
| `item_node` | Parse once, test for `channel->item`, root `item`, `entry` (Atom), `post`. Four cases, already enumerated as constants on `PartnerFeedLink`. |
| `data_type` | Determined empirically: read the feed both ways and compare which fields survive. See the corrected rule below. |
| `name` | `channel > title` |
| `website_url` | `channel > link` |
| `link_title` | Slugified `channel > title` |
| `rating_scale` | The `max` attribute on `<score>`, where present |
| Score availability | Does any item expose `score` or `note` (the only two `ImportByFeed::buildFromRss()` looks for)? |
| Score-in-description | If no score element, scan `<description>` for a `Rating: 8.0` shaped string and report it |
| Title rule + match rate | `TestTitleRule::suggestRule()` and `::test()`, already written, already operate on live-fetched titles |
| Date sanity | Is `pubDate` present and parseable by the importer's existing handling |

The score-in-description row is worth calling out: that is exactly the Casualvania diagnosis, done
by hand on 2026-07-06, reduced to something the tool reports in one click.

## Key feasibility finding: the existing pipeline works unsaved

`Loader::__construct()` takes a `PartnerFeedLink`, which looks like it forces a saved record
first. It does not. The Loader reads only `feed_url`, `data_type` and `item_node` off the model
(via `isParseAsObjects()` and the `item_node` switch in `generateItemsArray()`).

**An unsaved `new PartnerFeedLink()` with those three properties set works fine.** So the entire
live-fetch chain already used by the title rule tester -

```
Loader::loadByUrl() -> buildItemArray() -> ImportByFeed::buildFromRss() / buildFromAtom()
```

- can be driven against a pasted URL before any record exists. **No refactor of `Loader`,
`ImportByFeed`, `TestTitleRule` or `TitleMatchRate` is required.**

This is why the task is smaller than it looks. It is assembly of tested parts, not new parsing
capability. `FeedLinkTitleRuleController::getTitlesFromFeed()` is effectively the prototype.

## The parse mode rule was wrong (found 2026-07-27)

The rule recorded in the context notes, and repeated in the first draft of this document, was:

> A `<score max="10">8</score>` element (text value + attribute) requires Object parse mode -
> array mode drops the text value when an attribute is present.

**That is not what happens.** Verified directly against PHP 8.3:

| Item content | Array mode (`json_encode` round trip) | Verdict |
|---|---|---|
| `<score max="10">7.2</score>` | `{"score":"7.2"}` | **Text survives.** The attribute is dropped, the value is kept |
| `<score>7.2</score>` | `{"score":"7.2"}` | Fine |
| `<score max="10"></score>` | `{"score":{"@attributes":{"max":"10"}}}` | No text to lose |
| `<title><![CDATA[Name]]></title>` | `{"title":{}}` | **Text lost** |
| `<score max="10"><![CDATA[7.2]]></score>` | `{"score":{"@attributes":{"max":"10"}}}` | **Text lost** |

**CDATA is what breaks array mode, not attributes.** The Loader's own inline comment said as
much all along ("Don't do the JSON conversion for Wix sites or others using CDATA"); the prose
rule in the context notes had drifted from it.

Two consequences worth recording:

- **Nintendo Life, cited as the reason for the rule, is stored as Array / `channel > item`** and
  always has been. It is not on Object mode and does not need to be.
- **Casualvania does not need Object mode either.** Both modes were run end to end against the
  real feed: 37 items and 37 scores under each, identical titles, ratings and URLs.

Four live feeds genuinely do need Object mode - Switchaboo (22), Game Rant (43), TheGamer (44),
Hardcore Gamer (45) - and all four wrap their **item titles** in CDATA.

### Why detection compares fields instead of searching for CDATA

The obvious implementation ("body contains `<![CDATA[` -> Object mode") is also wrong.
**Nintendo Life's feed contains 25 CDATA sections** and runs correctly on array mode, because all
of them are in elements the importer never reads. A body-wide check would have moved a working
feed onto the other parse mode for no reason.

So `FeedProbe` parses the same body both ways and compares only the fields the importer actually
reads (`title`, `link`, `pubDate`, `score`, `note`), item by item. Anything present under Object
mode and missing under Array mode is something Array mode would silently lose. This cannot be
fooled by whichever `json_encode` quirk applies, including ones nobody has characterised yet.

## Design principles

**1. Suggest, never silently apply.** Every detected value lands in an editable field, and the
screen shows the *evidence* alongside the answer:

> Array mode loses `title` on this feed, most likely because the value is wrapped in CDATA.
> Object mode reads it correctly.

A silently-applied wrong `data_type` is the exact failure mode this area keeps producing. Showing
the reasoning also means the screen teaches the rule rather than hiding it.

**2. Fetch once, reuse the body.** The existing tester refetches the live feed on every debounced
keystroke. Acceptable for tuning one established link; rude to a new partner's server during an
onboarding session. Probe once, hold the parsed body for the request/session, tune the title rule
against the held copy.

**3. Load failures must be loud.** `Loader` uses a fixed `switchscores/v2.0` UA and
`verify => false`. Génération Nintendo sits behind a Cloudflare JS challenge and 403s every UA
including full Chrome. A dead or hostile feed must render as "could not load: <reason>", never as
an empty form that looks like a feed with no items.

## Build order

**1. `App\Domain\Feed\FeedProbe`** - read-only. Takes a URL, fetches once, returns a result object
carrying the detections above, each with its supporting evidence, plus a warnings list. No writes,
no records created.

**2. Validation command against the existing feed links** - see below. Fix whatever it exposes
before trusting the probe on anything new.

**3. Run it against Casualvania** - the first real case.

**4. Probe screen + prefilled create** - `/staff/reviews/feed-links/probe`. Paste URL, review
detections, create `ReviewSite` + `PartnerFeedLink` in one step via the existing
`ReviewSiteDirector` / `ReviewSiteBuilder` and `PartnerFeedLink\Repository::create()`. Should also
accept an existing site (adding a second feed to a site that already exists, as Seafoam has).

Steps 1-3 are the valuable, uncertain part and they unblock a waiting partner. Step 4 is the
fiddlier boilerplate and blocks nobody: with the probe's output, the existing add forms take five
minutes.

## Validate against the 49 existing feed links

**23 active and 26 inactive feed links already carry a known-correct `data_type` and `item_node`.**
That is a ready-made accuracy test, and it costs one artisan command.

Build `PartnerProbeExistingFeedLinks` (`--dry-run` by default, report only, writes nothing).
For each link: run the probe against its stored `feed_url`, compare detected vs stored, and
report one of three outcomes per feed.

| Outcome | Meaning |
|---|---|
| **Agree** | Detection is correct on a known case |
| **Disagree** | Either a detection bug **or** a misconfigured live feed. Both worth knowing. |
| **Could not load** | Feed is dead, blocked or moved. **Excluded from the accuracy figure.** |

The third row matters: dead feeds must not be counted as disagreements, or the accuracy number is
meaningless. Génération Nintendo (feed 31, marked Broken, Cloudflare challenge) will definitely
land here, and 26 of the links are inactive precisely because they went quiet.

**Why this step is not optional.** From the 2026-07-20 session: *"a heuristic built from a handful
of examples will look convincing and be wrong."* Two plausible heuristics were falsified that day.
Casualvania alone is a handful of one, and detection rules are exactly the kind of thing that
looks obviously right until run against real variety. There is also decent precedent for the run
finding real problems rather than just scoring the code: the last systematic pass through this
area turned up seven silently broken title rules.

## First case: Casualvania

Feed: `https://casualvania.com/feed/casualvania-reviews-switch/`

Reviewed 2026-07-06; the score was buried in the `<description>` CDATA as free text
(`Rating: 8.0`), which the importer cannot read. Alfredo was asked for a dedicated `<score>`
element matching the Nintendo Life format. **Confirmed done on 2026-07-27** (he chased twice
before this was picked up).

Current state of the feed, verified directly:

- HTTP 200, 18KB, `rss version="2.0"`, no auth or challenge
- **37 items, 37 with `<score max="10">`.** Full coverage, not just recent posts
- Item children are exactly `title`, `link`, `guid`, `pubDate`, `score`. The free-text
  `<description>` has been removed entirely rather than left alongside
- Scores run 4.5 to 9.7 at one decimal place (7.2, 8.8, 8.3 ...)

Expected probe output, which doubles as the acceptance test for step 3:

| Field | Detected | Evidence |
|---|---|---|
| `item_node` | `channel > item` (1) | `rss > channel > item` present |
| `data_type` | **Array** (1) | Nothing the importer reads is in CDATA, so the simpler mode suffices |
| `rating_scale` | 10 | `max="10"` |
| `website_url` | `https://casualvania.com/` | `channel > link` |
| Title rule | `^(.*) Review \(Nintendo Switch\)$`, index 1 | **37/37 titles match**, e.g. "Earth Atlantis Review (Nintendo Switch)" -> "Earth Atlantis" |

Two notes on this case:

- **`channel > title` is "Casualvania - Nintendo Switch Reviews", not "Casualvania".** The site
  name needs a human edit. A neat argument for principle 1: auto-derived is a starting point, not
  an answer. Worth a specific warning when the channel title looks like a feed name rather than a
  site name.
- **Decimal scores are fine.** `review_drafts.item_rating` is `decimal(4, 1)`, so one decimal
  place stores exactly. Checked rather than assumed, because Nintendo Life (the format this was
  modelled on) sends integers, so decimals were untested on this path. Anything with two decimal
  places would silently round, which is a reasonable thing for the probe to warn about.

The `default` case in `Parser::parseBySiteRules()` (strip platform text, strip "Review" text,
cleanup) should cover this feed, so no per-site code change is expected.

## What was built (2026-07-27)

| File | Purpose |
|---|---|
| `app/Domain/Feed/FeedFetcher.php` | The HTTP fetch, extracted from `Loader` so probe and importer send identical requests |
| `app/Domain/Feed/FeedProbe.php` | The detection. Read-only, no database |
| `app/Domain/Feed/FeedProbeResult.php` | Detections + evidence + severity-tagged warnings |
| `app/Console/Commands/Partner/ProbeExistingFeedLinks.php` | The validation run. Read-only |
| `tests/Unit/Domain/Feed/FeedProbeTest.php` | 24 tests, including the real Casualvania feed as a fixture |
| `tests/Unit/Domain/Feed/LoaderTest.php` | 5 tests, covering the single-item regression below |
| `app/Http/Controllers/Staff/Reviews/FeedLinkProbeController.php` | The screen: probe, then create both records |
| `resources/views/staff/reviews/feed-links/probe.twig` | The screen itself |
| `tests/Feature/Staff/FeedLinkProbeCreateTest.php` | 6 tests on the create step, the only part that writes |

Two small changes to existing files, both additive:

- `Loader::loadFromBody()` split out of `loadByUrl()`, so a body already fetched can be parsed
  more than once without re-requesting it. `loadByUrl()` now delegates to `FeedFetcher`.
- `ImportByFeed::setFeedLink()` split out of `setPartnerDetails()`, which also looks up the
  review site. The probe wants only the item builders, and works on a feed link with no site.

**Fast suite 450 -> 485, all green.** Page suite: the probe page added to `AsReviewsManagerTest`
and passing. Four unrelated Page failures pre-date this work (missing localdev ids, and a
`/c/switch-1/category` 301 from the `/browse/` URL migration).

### How the screen behaves

1. Paste a feed URL, press **Read the feed**. One fetch.
2. A "what the feed says" table lists each detection with the evidence for it, plus warnings
   and a few sample titles.
3. The create form below is prefilled and fully editable. Either create a new review site or
   attach the feed to an existing one (for a second feed on a site that already exists, as
   Seafoam has).
4. Submitting creates both records in a transaction, then **redirects to the title rule tester**
   with the live source selected.

Three deliberate choices:

- **Feed status defaults to Live** (the first option in the dropdown). It briefly defaulted to
  Test, and the reasoning recorded for both settings was wrong at different times - worth
  spelling out, because the pipeline is less manual than it looks. See below.
- **The redirect goes to the title rule tester, not the feed links index.** The probe can only
  say a rule matches the titles; whether those parsed titles find games is the question that
  decides whether the feed actually works, and it needs the database.
- **`review_import_method` is set, not offered.** The screen exists to onboard a feed.

The site name field carries a warning in its help text, because the channel title is usually the
name of the *feed* rather than the site, and `link_title` ends up in public URLs.

### A new Live feed publishes the same night, unattended

Established 2026-07-27 after Ben pointed it out. The nightly chain in `scripts/switchscores-cron`:

| Time | Command | Effect |
|---|---|---|
| 04:30 | `PartnerImportActiveFeeds` | Creates review drafts |
| 04:40 | `PartnerParseReviewDrafts` | Applies the title rule, sets `game_id` |
| 04:45 | `ReviewConvertDraftsToReviews` | **Publishes a review link** for every complete draft |

`ReviewDraft\Repository::getReadyForProcessing()` takes any draft with `game_id`, `item_url`,
`site_id`, `item_date` and `item_rating` set and no `process_status`. `ConvertToReviewLink`
then creates the review link and **updates the game's `review_count` and `rating_avg`**. There
is no human step anywhere in that path.

So drafts are not a review queue in the general case. **They are a queue only for the ones that
came out incomplete.** A feed that probes cleanly - which is exactly what this screen is built
to produce - is the case that skips the queue entirely.

**The risk is not an unmatched title, it is a wrongly matched one.** A rule that matches nothing
leaves harmless drafts sitting in the queue. A rule that matches the *wrong* game publishes a
real review with a real score against it and shifts that game's average. That is the case worth
five seconds on the title rule tester, which is why the create step redirects there.

`allow_historic_content` multiplies it: the whole back catalogue converts in one night, so a
mismatching rule is wrong across dozens of games at once rather than once.

Duplicates are safe - `ConvertToReviewLink::processItem()` checks `byGameAndSite()` first and
marks repeats `Duplicate` rather than creating a second link.

**Live is still the default**, as Ben's call: he sees the probe output and the tester before the
04:30 run, so the check happens at onboarding time rather than being deferred to a second visit.
The point of writing this down is that the safety does not come from the draft stage - it comes
from looking at the title rule.

### Not covered by tests

The create step runs in a transaction so a rejected feed link cannot leave an orphaned review
site behind. **The rollback itself is not covered.** Validation runs before the transaction
opens, so the tested case never reaches it, and a database-level failure on the second insert
could not be provoked through the HTTP layer: there is no unique constraint on
`partner_feed_links` to violate, and MySQL coerces bad types rather than rejecting them. The
transaction is defence in depth. Recorded here rather than left as an assumption, because the
test name would otherwise imply more coverage than exists.

### Validation run results

All **23 live feed links** pass: their stored settings read their feed correctly.

Across all **49** feed links (`--include-inactive`): **37 OK, 0 problems, 12 unreachable.** The
12 are dead or blocked partner sites, all already archived - six fail at the network layer, six
return HTTP 200 carrying HTML rather than a feed.

The probe's own choice was compared against the stored value on the feeds where it matters most
(the four Object-mode feeds, plus Nintendo Life and Video Chums): **6 of 6 agree.**

Note that "unreachable" deliberately includes a 200 response that is not a feed. Counting those
as failures would make the pass rate a measure of how many partners are still trading rather
than of whether detection works.

### Bug found and fixed: single-item feeds in Array mode

Surfaced by an Atom test case, and **pre-existing rather than introduced here.**

`json_encode` of a SimpleXML document cannot express a list of one. A feed holding a single item
decodes to that item's own fields (`['title' => ..., 'link' => ...]`) rather than a list
containing one item, so `generateItemsArray()` iterated the **fields**. A one-item feed produced
**four "items", each a bare string.** Object mode was unaffected, because SimpleXML iterates a
single child correctly - so the same feed behaved differently depending only on its parse mode.

Fixed with `Loader::asItemList()`, which wraps a non-list in a list. Verified by reverting the
fix and confirming the new test failed with exactly the right symptom (4 items instead of 1)
while the others stayed green.

Rare in practice, because feeds normally carry a page of items. But it is exactly the shape a
**new or quiet partner feed** arrives in, which is precisely when a new site is being onboarded
and nobody is watching closely.

## Open questions

- **`feed_url_prefix` and `allow_historic_content`** are not derivable from the feed. Leave them
  to the form with sensible defaults. `allow_historic_content` matters for a first import of a
  back catalogue: Casualvania's oldest items go back well beyond the usual import window, and 37
  items with scores is a genuinely useful backfill rather than noise. Decide deliberately.
- **Should the probe ever write?** Preference: no. A read-only probe is safe to point at anything,
  including a partner's feed mid-conversation, and safe to run in bulk across all 49 links.
  Creation stays an explicit, separate action.
- **Atom coverage.** `ITEM_NODE_ENTRY` exists and `buildFromAtom()` is implemented, but Atom feeds
  carry scores differently, if at all. Detect and report the node type; do not over-invest in Atom
  score detection until a real Atom partner turns up.

## Out of scope

- Partner-facing self-serve onboarding. Rejected 2026-07-06: too rare, and feeds vary enough to
  want a human eyeball. Nothing here changes that.
- A published feed-spec page for partners to build against. Still a reasonable idea, still
  separate. The probe's warnings are arguably the better source of truth for such a page, since
  they describe what the importer actually accepts rather than what it is documented to accept.
