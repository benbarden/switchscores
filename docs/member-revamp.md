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
| 82 | Hide Format/Hours when adding, show on edit | Low | |
| 83 | Set owned date: today, custom, or ignore | Low | |
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
2. **#82 + #83** - Games collection UX fixes (quick wins)
3. ~~**#120** - Nav restructure (improves navigation)~~ Done
4. **#94** - Staff approve/deny workflow
5. **#100** - Collection quick status changes
6. Then consider **#19** (open signup)

## Nav Restructure (#120) - DONE 2026-03-08

### Implementation

**Primary nav (dark blue):**
`Members | Developers | Reviewers | Games companies | Staff | Logout`
- Active section highlighted
- No more dropdowns

**Secondary nav (light blue, contextual):**
- **Members:** Dashboard | Collection | Quick reviews | Settings
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
