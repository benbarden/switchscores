# Switch 2 pricing: telling an upgrade pack from a deluxe edition

Scoping notes. Not started. Analysis done 2026-07-20.

Four games are currently held on manual prices with `ignore_price` as a **temporary** measure
(flag `price-upgrade-pack`). This task removes the need for that hold.

## The problem

For Switch 2, `Parser::parsePrice()` prefers `price_sorting_f` over `price_regular_f`. That rule
was added in June because `price_regular_f` is sometimes the **deluxe edition** price while
`price_sorting_f` holds the base edition.

But `price_sorting_f` is really "**the cheapest purchasable SKU**" - it is the field Nintendo
sorts search results by. For a Switch 2 Edition with an **upgrade pack**, the cheapest SKU is the
upgrade pack, not the game. So the rule publishes the upgrade price as the game's price.

Found live on prod 2026-07-20:

| game | site showed | actual RRP |
|---|---|---|
| Kirby and the Forgotten Land S2 Ed | £16.99 | £66.99 |
| Metroid Prime 4: Beyond S2 Ed | £7.99 | £58.99 |
| Xenoblade Chronicles X: Definitive Ed | £4.19 | £54.99 |
| Fruit Mountain Party S2 Ed | £2.49 | £13.49 |

All four are now manually corrected and frozen with `ignore_price`. **Those freezes must be
removed once this is fixed** - find them via the `price-upgrade-pack` flag.

## Do NOT revert the June rule

Of **212** Switch 2 records not on sale (both price fields present):

| | count | effect of the `sorting_f` rule |
|---|---|---|
| `sorting_f` == `regular_f` | **191** | no difference |
| deluxe-ish (ratio 0.5-0.99) | **14** | **correct** - use `sorting_f` |
| upgrade-ish (ratio <0.5) | **7** | **wrong** - should use `regular_f` |

The rule only matters for 21 records and is right for 14 of them. Reverting fixes 7 and breaks
14. The fix must **discriminate**, not flip the default.

## The ratio heuristic does NOT work

The obvious approach - treat a large gap between `sorting_f` and `regular_f` as "this is an
upgrade pack" - looked good until it was checked against reality.

**Counter-example: Virtua Fighter 5 R.E.V.O. World Stage (game 17373).**
`regular_f` 39.99, `sorting_f` 15.99, ratio **0.40**. It looks exactly like an upgrade-pack case
and it is not: the base game is £15.99 on Switch 2 and the **30th Anniversary Edition** is
£39.99. This is a deluxe case, `sorting_f` is correct, and the current code already gets it
right. A 0.5 threshold would have **broken a price that was correct**.

Verified against the eShop by Ben, 2026-07-20. Before that check, the deluxe band looked like
0.73-0.85 with clear space beneath it - that apparent gap was an artefact of a six-example
sample. **Do not resurrect the threshold idea without a much larger validated sample.**

## Signals evaluated so far

| signal | catches the 7 | false positives | verdict |
|---|---|---|---|
| `sorting_f` < 50% of `regular_f` | 7 of 7 | breaks Virtua Fighter (a correct deluxe case) | rejected |
| ≥2 entries in `nsuid_txt` | 6 of 7 | **97** not-on-sale records | too broad alone |
| "Switch 2 Edition" in the title | 6 of 7 | **74** not-on-sale records | too broad alone |

Neither NSUID count nor the title is sufficient: there are 74 Switch 2 Editions and only 6 are
mispriced, so being an edition does not predict a bad price. Both also miss Virtua Fighter, which
has no "Edition" in its title.

## SOLVED: per-SKU prices exist (2026-07-20)

**`https://api.ec.nintendo.com/v1/price?country=GB&lang=en&ids=<nsuid,nsuid,...>`** returns prices
**per NSUID**. This dissolves the problem rather than working around it - no heuristic needed.

Per NSUID it returns `sales_status`, `regular_price`, and (when on sale) `discount_price` with
`start_datetime` / `end_datetime`.

**The NSUID prefix is the discriminator** that could not be found in the scalar price fields:

| prefix | meaning |
|---|---|
| `70010000…` | the standalone game - **this is the price we want** |
| `70050000…` | upgrade pack |
| `70070000…` | deluxe / premium edition bundle |

**Validated 7 for 7** against the eShop-confirmed cases, including both deluxe counter-examples
that killed the ratio heuristic:

| game | `7001` price | other SKU | site showed | verdict |
|---|---|---|---|---|
| Kirby S2 (15023) | **66.99** | 7005: 16.99 | 16.99 | fixed |
| Metroid Prime 4 (16689) | **58.99** | 7005: 7.99 | 7.99 | fixed |
| Xenoblade X (17190) | **54.99** | 7005: 4.19 | 4.19 | fixed |
| A-Train S2 (16695) | **59.38** | 7005: 5.39 | 5.39 | fixed |
| 4PGP (17109) | **22.49** | 7005: 4.49 | 4.49 | fixed |
| Fruit Mountain Party (17464) | **13.49** | *(none)* | 2.49 | fixed |
| Street Fighter 6 (15016) | **34.99** | 7007: 49.99 | 34.99 | correctly preserved |
| Virtua Fighter 5 (17373) | **15.99** | 7007: 39.99 | 15.99 | correctly preserved |

**Fruit Mountain Party is the case that proves inference could never have worked.** It carries a
single NSUID (`70010000121555`, £13.49) and `price_sorting_f` of £2.49 - a price that appears
**nowhere** in `nsuid_txt` or the price API. `sorting_f` reflects an SKU the search payload does
not otherwise carry. It also defeats the NSUID-count signal (only one NSUID).

**`price_regular_f` can be contaminated too.** On-sale example: search payload gave `regular_f`
4.99 / `lowest_f` 14.99 / 40% off - incoherent, the "sale" price exceeds the "regular" price. The
price API resolves it cleanly: game `70010000111806` regular **£24.99**, discounted **£14.99**
(40% off £24.99 = £14.99 exactly); the £4.99 is the `70050000065152` upgrade pack leaking into
`price_regular_f`. **Neither scalar field held the right answer**, so this record was unrecoverable
by any rule operating on the existing payload.

### Confirmed scale on PROD (dump `switchscores_prod_260720-3.sql`, 2026-07-20 17:10)

Swept all Switch 2 records: took each record's `70010000…` NSUID, compared the price API's
`regular_price` against the **live `games.price_eshop`** (not `data_source_parsed.price_standard`
- that is staging, and comparing against it inflates the count badly).

| | count |
|---|---|
| agree | 283 |
| **wrong live price - actionable** | **56** |
| on sale, sale price right but RRP wrong | 23 |
| no `7001` SKU (skipped) | 1 |

**Of the 56, only 28 carry a `price-check-deluxe` flag. The other 28 have no flag at all** and
would never have surfaced through the review queue. **26 of those 28 carry a `70050000…` upgrade
SKU.** Worst cases: Xenoblade Chronicles: Definitive Edition £7.99 (really £58.99), Atelier Yumia
£5.00 (£54.99), R-Type Tactics I・II Cosmos £4.49 (£49.48), Fitness Boxing 3 £7.99 (£49.99),
Culdcept BEGINS £8.99 (£44.99).

**Every error understates the price** - £1,142 total across 56 games, median £14, max £54.99. On
an affiliate site the bias is consistently toward looking cheaper than reality.

Spot-checked against the live site before trusting the sweep: NBA 2K26 renders £6.99 against a
real £69.99, Pokémon Legends: Z-A £7.99 against £58.99, Pokopia £29.99 against £58.99.

### Scale of the affected cohort (earlier localdev estimate, superseded)

Cross-tab of NSUID prefix against price-field shape, Switch 2 raw records (sentinel excluded):

| | `reg==sort` | `sort<reg` | `sort>reg` |
|---|---|---|---|
| has `7005` (upgrade) | 82 | **12** | 10 |
| has `7007` (deluxe) | 17 | 22 | - |
| neither | 256 | 18 | - |

The **12** `7005` + `sort<reg` records are the likely broken cohort - **double the 6 currently
flagged**, consistent with the afternoon read that this is the dominant failure mode. The 22
`7007` + `sort<reg` are the deluxe cases where the June rule is right. Note the shapes are
identical, which is exactly why the ratio heuristic failed. (Caveat: this cross-tab does not
exclude on-sale records, where `sort<reg` is expected anyway - the counts are indicative, not
final.)

### Ideas superseded by the above

- **`title_extras_txt` / `title_master_s`** - not needed.
- **An absolute floor** - not needed.
- **Flag rather than guess** - not needed for this; the price API is authoritative.
- **`title_extras_txt` / `title_master_s`** may distinguish an upgrade-pack listing from a
  standalone edition. Not yet examined.
- **An absolute floor** rather than a ratio - upgrade packs cluster at low absolute prices
  (£2.49-£16.99). Weak on its own; Virtua Fighter's £15.99 base sits inside that range.
- **Flag rather than guess.** Given only ~21 records are affected, auto-flagging the ambiguous
  ones for manual confirmation may beat any automatic rule. Fits the monitoring model in
  `game-flags-data-quality-monitoring.md`.

## Related

- **#132** (`docs/improvements.md`) - the **Switch 1** half of the same problem: S1 games with a
  deluxe edition have `price_regular_f` inflated while `price_sorting_f` holds the correct
  standard price (e.g. Dark Auction, game 17079). S1 is not covered by the Switch 2 rule at all.
  Whatever discriminator is found here should be evaluated for S1 too.
- **#44** (edition field + S1/S2 linking) - **adjacent but will not fix this.** Worth doing for
  unique-game counts, cross-console linking and the "Also on" section, but edition status does not
  predict a mispriced record (74 editions, 6 problems). Do not justify #44 on pricing grounds.

## Also unlinked

`link_id` 3037228, "The Fox's Way Home - Nintendo Switch 2 Edition" (`regular_f` 15.99,
`sorting_f` 2.49) has the same defect but no `game_id` yet, so it is not on the site. It will
import at £2.49 when linked unless this is fixed first.
