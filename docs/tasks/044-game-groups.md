# Game groups: link Switch 1 / Switch 2 versions of the same game

Improvement #44. Model agreed 2026-09-27; step plan agreed 2026-10-21.

**Status:** steps 1 and 2 deployed 2026-10-21 (prod checked: staff API 401, sub-page 401 anonymously, public pages 200). Suggested order: step 4, then step 3 (not yet confirmed). Step 5 not started.

## Problem

Cross-console linking is an exact `link_title` match only: `Repository::getByLinkTitleOnOtherConsole()`
(`app/Domain/Game/Repository.php`), called once, from `GameShowController` for the "Also on" box
on the public game page. It misses pairs where the Switch 2 title carries "Nintendo Switch 2
Edition" or differs slightly in punctuation, and it returns `first()`, so it can't say anything
about the pair beyond "same link title".

## Numbers (localdev, 2026-09-25 prod dump)

- 513 Switch 2 games; **141** match a Switch 1 game on `link_title` today.
- A normalised title match finds **239** - **98** more. Most are "Nintendo Switch 2 Edition"
  titles (Zelda: TotK and BotW, Civilization VII, No Man's Sky, Hades II, Exit 8...).
- No game matched on `link_title` but not on the normalised title.
- **False positive found:** with all punctuation stripped, "Dragon Quest I & II HD-2D Remake"
  matched "Dragon Quest III HD-2D Remake". The normaliser must keep word boundaries and turn `&`
  into "and". This is why normalised matches are suggestions, never linked automatically.

## Model (agreed 2026-09-27)

- `game_groups`: `id`, `name`, `match_type`, `note`, timestamps.
- `games.game_group_id`: nullable, indexed.
- Games sharing a group are the same game on different consoles. Chosen over a per-game pointer
  to the counterpart: nothing to keep in step, group-level notes have a home, and it still works
  if a third console arrives.
- `match_type`: `identical` (same game, different console) or `enhanced` (a Switch 2 Edition with
  material differences, e.g. Super Mario Bros. Wonder's extra content) - still linked, but noted
  as not identical.
- Titles stay per game and are never forced to match. The groups page surfaces differences
  instead, so each one is either fixed or deliberately kept. For `enhanced` groups the title
  decision is per group.

## Steps

Each step deploys on its own.

1. **Schema and domain.** Migration for `game_groups` and `games.game_group_id`; `GameGroup`
   model and repository. A shared `TitleNormaliser` (case, punctuation, `:` vs `-`, `&` to "and",
   word boundaries kept, the "Nintendo Switch 2 Edition" / "(Switch 2)" suffixes stripped). It
   takes over `CategorySuggester::stripConsoleSuffix()`'s suffix list so there is one copy.
   Tests include the Dragon Quest case. No visible change.
   **Built 2026-10-21:** migration `2026_10_21_000001_create_game_groups_table` (`game_groups`,
   `games.game_group_id` with a foreign key, `nullOnDelete`); `App\Models\GameGroup`
   (`MATCH_IDENTICAL` / `MATCH_ENHANCED`, `games()`); `Game::gameGroup()`;
   `App\Domain\GameGroup\Repository` (create, find, getAll, linkGame, unlinkGame - a group
   left with fewer than two games is deleted and the last game unlinked). The key class is
   `App\Domain\GameGroup\TitleMatchKey`, not `TitleNormaliser`: that name is taken by
   `WeeklyBatch\TitleNormaliser`, which cleans imported titles for display. `forTitle()` strips
   the console suffix (case-insensitive), lowercases, joins apostrophes, turns `&` into "and"
   and every other non-letter/digit run into one space. `stripConsoleSuffix()` replaces
   CategorySuggester's private copy and adds `' - Nintendo Switch 2 Edition'` (it used to leave
   "Hades II -"). Tests: `TitleMatchKeyTest` (13, real titles), `RepositoryTest` (4). Migration
   rolls back cleanly. **Run over localdev's data:** 141 `link_title` pairs, 238 key matches,
   97 new from the key, 0 ambiguous; Dragon Quest I & II (16423) matches its own Switch 1
   version (16473), not III (13840).
2. **Linking from game detail.** A "Link to other console version" search on the staff game
   detail page, using `/api/game/find-by-title` (staff-only since #152). Creates a group or
   joins the other game's existing one; unlink too.
   **Built 2026-10-21.** **Editing is a sub-page, not on detail (Ben, 2026-10-21)** - same
   pattern as Tags and Publishers. Staff game detail shows "Other console versions" read-only
   in the middle column (`detail/game-group.twig`: the other versions linked to their staff
   pages and a match type badge, or "Not linked."; hidden in the reviewers view, which reuses
   that column); its "Linked to" label opens the sub-page `staff/games/game-group/{gameId}/edit`
   (`game-group/edit.twig`). Ungrouped: a select2 search on `/api/game/find-by-title`, filtered
   to the other console, and a match type (pre-set to `enhanced` when the title says "Switch 2
   Edition"). Grouped: the group name and match type, the other versions, and "Unlink this
   game". Routes `staff.games.gameGroup.edit` (GET) and `.link` / `.unlink` (POST), games
   manager. A link or unlink returns to game detail with the usual "Successfully edited"
   alert; a refused link returns to the sub-page with the reason. Rules in `App\Domain\GameGroup\Linker`: two ungrouped games get a new group
   named after the Switch 1 title without its console suffix; if one is grouped, the other
   joins and the group keeps its match type; refused (with the reason shown) for the same game,
   the same console, a group that already has a game on that console, two different groups, or
   an unknown match type. Core-data cache cleared for each game touched. The group is bound in
   the controller, not read as `GameData.gameGroup` - for an ungrouped game Twig falls back to
   the `gameGroup()` relation object, which is truthy. Tests: `LinkerTest` (7),
   `GameGroupLinkTest` (4: detail is read-only and links to the sub-page, sub-page shows the
   form, link then unlink, refusal returns to the sub-page with the reason).
3. **Groups page and suggestions.**
   - Staff groups page: lists groups, flags groups whose titles still differ after normalising.
   - **Backfill (Ben, 2026-10-21):** the existing exact `link_title` pairs (141 on the 25 Sep
     dump) get groups automatically as `identical`. An exact match is already what the live
     "Also on" box trusts, so this adds no new risk. Any that should be `enhanced` are changed
     from the groups page.
   - Suggestions list: unlinked Switch 2 games with a likely Switch 1 match from the normalised
     title, confirmed one at a time with one click. `match_type` pre-filled as `enhanced` when
     the title says "Switch 2 Edition".
   **Built 2026-10-21 (localdev, uncommitted):** `App\Domain\GameGroup\Matcher` finds pairs
   among ungrouped games - `link_title` first, then the title key - pairing a Switch 2 game
   only when exactly one ungrouped Switch 1 game matches (more is counted as ambiguous).
   **Backfill:** `php artisan GameGroupsBackfillLinkTitlePairs` (dry run unless `--apply`;
   DB dump first on prod) groups the `link_title` pairs as `identical` through `Linker` and
   clears both games' core-data cache. Localdev dry run: 141 pairs, 0 ambiguous. **Pages**
   (linked from the staff games dashboard under Weekly updates): `/staff/games/game-groups`
   lists every group, rows whose titles still differ after normalising first and highlighted,
   each with an Edit link to the game's sub-page; `/staff/games/game-groups/suggestions` lists
   the pairs with a match type (pre-set to `enhanced` for "Switch 2 Edition" titles) and a
   Confirm button per row. Localdev: 237 suggestions before the backfill (141 link title + 96
   title key - Hades II already linked), 0 ambiguous. **Match type can now be changed** on the
   game's sub-page (`staff.games.gameGroup.matchType`) - needed because the backfill makes
   everything `identical`. No "dismiss" for a wrong suggestion yet: it stays on the list until
   linked elsewhere. Tests: `MatcherTest` (4), `GameGroupLinkTest` +2 (match type change;
   suggestions confirm and groups page).
4. **"Also on" uses the group first,** falling back to the title match. The fallback goes once
   linking is complete.
   **Built 2026-10-21 (localdev, uncommitted):** `GameShowController` asks
   `GameGroup\Repository::getOtherConsoleGame()` (active games only, like the fallback), then
   falls back to `getByLinkTitleOnOtherConsole()`. **Note text (Ben, 2026-10-21):** no note for
   `identical`; for `enhanced`, a small muted line under the title - "Enhanced Switch 2 Edition"
   on the Switch 1 page, "Original Switch 1 version" on the Switch 2 page. "Enhanced" matches
   the type name and holds whether the extras are free or a paid upgrade pack. Tests:
   `GameAlsoOnTest` (enhanced notes both sides, identical has none, link_title fallback).
   **Timing:** public game pages are edge-cached at Cloudflare for 24 hours
   (`CacheAnonymousPages`, `s-maxage=86400`) and there is no purge in the app, so a new link
   reaches anonymous visitors within a day - same as any other game edit. The Redis core-data
   cache is cleared on link/unlink, so logged-in views update at once.
5. **Where groups get used next** - planned once groups exist: unique-game counts,
   CategorySuggester's other-console lookup, then #161 (store editions) and #132 (Switch 1
   prices).

## Not a pricing fix

See `docs/tasks/switch-2-upgrade-pack-pricing.md` ("Do not justify #44 on pricing grounds").
"Edition" in #161 means a store purchase option; here it means the same game on another console.

## Related

- The weekly update converts "Nintendo Switch 2 Edition" to "(Switch 2)" in titles, which the
  cleanup command then removes, so newer Switch 2 titles often already match. Older imports
  still carry the full suffix.
- #121 ("Also on"), #161, #132.
