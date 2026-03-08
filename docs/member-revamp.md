# Member Tools Revamp

Planning document for improving member experience before opening registration.

## Overview

Goal: Tidy up member tools before making signup easier (#19).

## Related Tasks

### Quick Reviews
| # | Task | Complexity | Status |
|---|------|------------|--------|
| 66 | Submit quick review without signing up | High | Needs good signup first |
| 94 | Quick reviews: approve/deny, not Edit (staff) | Medium | |
| 97 | Show recent quick reviews on homepage | Low | Needs more reviews first |
| 85 | Delete review draft | Low | |

### Games Collection
| # | Task | Complexity | Status |
|---|------|------------|--------|
| 82 | Hide Format/Hours when adding, show on edit | Low | Done 2026-03-08 |
| 83 | Set owned date: today, custom, or ignore | Low | Done 2026-03-08 |
| 100 | Quick status changes | Medium | |

### Member Account/Dashboard
| # | Task | Complexity | Status |
|---|------|------------|--------|
| 53 | Edit display name, email, password | Medium | Done 2026-03-08 |
| 102 | Onboarding dismissable banner | Medium | |
| 103 | Upload/edit avatar | High | |
| 99 | Member profiles | High | |
| 120 | Nav restructure with secondary nav bar | Medium | Done 2026-03-08 |

### Signup (do last)
| # | Task | Complexity | Status |
|---|------|------------|--------|
| 19 | Make registration open | Medium | |

## Suggested Order

1. ~~**#53** - Basic account editing (foundational)~~ Done
2. ~~**#82 + #83** - Games collection UX fixes (quick wins)~~ Done
3. ~~**#120** - Nav restructure (improves navigation)~~ Done
4. ~~**Discovery Hub Phase 1** - Find a game + Wishlist + Hidden games~~ Done
5. **Discovery Hub Phase 2** - Saved searches (next!)
6. **#94** - Staff approve/deny workflow
7. **#100** - Collection quick status changes
8. Then consider **#19** (open signup)

## Discovery Hub - NEW DIRECTION

### The problem
Public pages are cached (bot traffic), so we can't personalise them for logged-in users.

### The solution
Make the **member dashboard** the personalised discovery hub. Primary CTA: **"Find me a game"**

### Phase 1 - DONE 2026-03-08

**Find me a game** (`/members/find-game`)
- Keyword search
- Category filter (hierarchical)
- Console filter (Switch 1/2)
- Minimum rating
- Player count (2+, 3+, 4+)
- Local multiplayer / Online play checkboxes
- Play modes (TV, Tabletop, Handheld)
- "Ranked games only" filter (default on)
- "Hide games I own" filter
- Results as cards with "Own it" / "Want it" / "Hide" buttons

**Wishlist** (`/members/wishlist`)
- Games you want to buy
- "Got it!" moves to collection (auto-removes from wishlist)
- "Remove" button

**Hidden games** (`/members/ignored-games`)
- Games hidden from search results
- "Unhide" button

**UX improvements**
- Return to search after adding to collection
- Nav: Dashboard | Find a game | Wishlist | Collection | Add to collection | Quick reviews | Settings

### Phase 2 - Saved Searches (next)
- Save any search with a name
- Dashboard shows results of saved searches
- "3 new games match your 'Local co-op platformers' search"

### Phase 3 - Email notifications (future)
- Notify users of new releases/reviews matching saved searches
- Opt-in per saved search

## Nav Restructure (#120) - DONE 2026-03-08

### Implementation

**Primary nav (dark blue):**
`Members | Developers | Reviewers | Games companies | Staff | Logout`
- Active section highlighted
- No more dropdowns

**Secondary nav (light blue, contextual):**
- **Members:** Dashboard | Find a game | Wishlist | Collection | Add to collection | Quick reviews | Settings
- **Developers:** Dashboard | API guide | API methods | API tokens | Switch Weekly | Hanafuda Report
- **Reviewers:** Dashboard | Your reviews | Feed health | Stats | Unranked games | Edit profile
- **Games companies:** Dashboard | Edit profile

**Files changed:**
- `resources/views/theme/member-b5/base.twig` - nav structure
- `public/member-b5/custom.css` - navbar-primary/navbar-secondary styles

**Also migrated 8 pages from B3 to B5:**
- Collection: index, add, edit, list, top-rated-by-category, category-breakdown
- Campaigns: show
- Featured games: add
- Plus related components (form.twig, table.twig, grid-item.twig)

## Collection UX (#82 + #83) - DONE 2026-03-08

### Add form simplified
- Only shows: Play status + "When did you get it?" (Skip/Today/Choose date)
- Owned type and Hours played removed (available on edit)
- Defaults: "Not started" play status, "Skip" for owned date

### Edit form unchanged
- All fields available: play status, owned type, owned from, hours played

### Form redesign
- Left-aligned layout (labels above fields)
- Play status as coloured button tiles (3 per row)
- Packshot displayed on right (250px)

### Quick Add promoted
- Renamed from "Quick add (beta)" to "Add to collection"
- Added to secondary nav
- Fixed bug: `not_started` → `not-started`

### Files changed
- `resources/views/members/collection/form.twig` - form layout
- `resources/views/members/collection/scripts.twig` - JS for date picker toggle
- `app/Http/Controllers/Members/CollectionController.php` - owned_from_option handling
- `resources/views/members/collection/quickAdd.twig` - fixed status bug, updated text
- `resources/views/members/index.twig` - consolidated quick links
- `resources/views/theme/member-b5/base.twig` - added nav link
- `public/member-b5/custom.css` - play status button styles
