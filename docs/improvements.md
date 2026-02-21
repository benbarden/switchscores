# Switch Scores - Potential Improvements

This document tracks potential improvements, features, and enhancements for the Switch Scores project.

**Next ID: 111**

---

## Session Log

### 2026-02-20: Initial Review

**Logged & Validated:** 110 improvement ideas from Asana backlog

**Organized by priority:**
- 7 High priority (bugs + foundational changes)
- 34 Medium
- 47 Low
- 7 Needs decision
- 16 Merged/Killed/Done

**Key outcomes:**
- Identified real bugs (#7 tag URLs, #41 title hash, #106 duplicate DataParsedItem)
- Spotted strategic decisions needed (console-split strategy for #3, #4)
- Found Claude Code workflow opportunities (mass tagging, scraping, review imports)
- Designed #110 (unified crawl queue) - see `docs/tasks/110-game-crawl-queue-system.md`

**High priority items:**
1. #7 - Tag URL bug (duplicate link_titles)
2. #8 - Companies search performance (3k records)
3. #11 + #30 - S2 URL handling (do together)
4. #22 - Game status field (Delisted/Soft delete)
5. #106 - Duplicate DataParsedItem bug
6. #110 - Unified game crawl queue

---

## High Priority

| # | Idea | Complexity | Notes | Your Notes |
|---|------|------------|-------|------------|
| 7 | Tags: don't allow two with the same URL | Low | Add unique constraint + validation | Actual bug - duplicate link_titles cause wrong tag to display. Games on 2 tags but only 1 shows. |
| 8 | Games companies: search without needing to view all | Medium | Currently loads all - add search filtering | 3k companies - full list is slow. Performance issue. |
| 11 | URLs to include console name / unique title check to use console | Low | Switch 2 handling exists in parsed items | Allow same title on both consoles. Currently S2 titles blocked if S1 exists, forcing "(Switch 2)" suffix. Need per-console unique check. Related to #30. |
| 22 | Add game status for Delisted, (Soft) deleted | Medium | Add dedicated status enum field; migrate from format_digital | First-class field separate from API-driven format. Handle sync issues (API breaks, scripts fail). Soft delete for #31 (410 status). Hard delete still needed for accidents. De-listed hidden on v2 pages. |
| 30 | Change Switch 2 game URLs. Allow S1/S2 same title | High | URL structure change affects 6+ controllers + 301 redirects | Core URL change. #11 is part of this - do together. Foundational for S2 title handling. |
| 106 | BUG: Duplicate DataParsedItem records for same console | Medium | Console detection not always working | Sometimes creates two records for same console. Needs investigation. |
| 110 | Unified game crawl queue system | High | New infrastructure | Solves #70, #78, #109 together. One crawl per game: images, 404s, missing data. Track last_checked, space requests (50-100/few hours), manual/periodic re-queue. |

---

## Medium Priority

| # | Idea | Complexity | Notes | Your Notes |
|---|------|------------|-------|------------|
| 5 | Change category to allow drill-down by tag | Medium | `gamesByCategoryAndTag()` exists but no UI | Categories collapsed into tags (e.g. Picross under Puzzle). 1 category per game, multiple tags. Show only tags with games in that category. Useful for discovery. |
| 6 | Tags: add support for layout v2 | Medium | Field/enum exist - need Twig template | V2 works well for categories. Needs templates + queries. Requires on-page intro + meta descriptions per tag. Gradual rollout once available. |
| 9 | Add data checks as global lists | Medium | IntegrityCheck methods exist - need staff pages | GameDetail checks (category, players, price etc) rolled up to dashboard. Show totals, click through to fix. Could use Claude to scrape/backfill. |
| 10 | Scrape publisher name, players, and other info from Nintendo URL | High | Needs new scraper class + DOM parsing | For backfilling - new games covered. Page structure fairly stable. Already doing this in another Claude Code project. |
| 12 | Table sorting is broken on one staff page | Low | Need to identify which page | Page: /staff/data-sources/nintendo-co-uk/ignored. JS/DataTables broken. Shows entire dataset without pagination. |
| 14 | Show raw/parsed item data on Game Detail | Medium | Data exists - expose on staff view | Tab or linked page. See raw data for new fields we could use. Link to items from game detail, drop full list. Includes #91, #92. |
| 15 | Data source items: staff pages | Medium | Basic pages exist - need comprehensive CRUD | Remove full DS raw list (slow). Link raw/parsed detail from Game Detail. Link from ignored items lists. Supersedes older DS ideas. |
| 17 | Action list for games without a custom description | Low | Simple query + list view | On-page descriptions for SEO (thin content fix). Not copied from Nintendo to avoid duplicate content. Check if also in meta. |
| 18 | Tag categories: show groups on categorisation dashboard | Low | Dashboard exists - add grouping | Goal: every game has 1+ tag from each tag category (Game type, Gameplay, Content, Mood, Visual style, Viewpoint). Dashboard shows progress. Claude can help with mass tagging. |
| 19 | Make registration open. Keep invite codes for partners etc | Medium | Invite code validation exists; add open registration toggle | Spam concern. Drop Twitter login around same time. Shore up member tools first. |
| 28 | Update New releases page to new layout | Medium | V1 template exists; create v2 with stats/featured sections | High traffic page. Simple list currently. Could add affiliate links, more info. Not same as category v2. |
| 29 | Update homepage to new layout | Medium | Refactor to unified bindings + v2 layout | Needs refresh, been same for a while. Open to ideas. Could incorporate ones-to-watch, featured, etc. |
| 31 | Hold deleted URLs; send 410 status to Google | Medium | Need deleted_urls table + handler logic | For SEO. Games only - others via .htaccess. Consider alongside #22 (soft delete). |
| 41 | Update the title hash when editing a game's title | Low | Hash system exists; hook into game edit save | Bug: old title hash blocks other games. Currently requires manual hash edit. |
| 42 | Event/log when game hits 3 reviews | Medium | No event system for review milestones; needs dispatcher | 3 reviews = ranking threshold. Surface "newly ranked" across homepage, reviews, members, staff. |
| 44 | Add edition field to games + link S1/S2 versions | Medium-High | Requires migration, model, editor UI, linking | For "Switch 2 Edition" games. Link to S1 version. Helps count unique games. Becoming more common. |
| 49 | Games companies: create dashboard and missing data filters | Medium | Dashboard exists; add missing data filter queries | For outreach - find companies to contact. Some may exist already - needs review. |
| 50 | GH118: public companies page improvements | Medium | Public profile exists; enhance layout/data | Searchable list, recent games by publisher, avg score, "Claim this page" CTA, show if company is engaged. |
| 51 | View games company signups | Medium | Signup model exists; create staff list view | No list page currently - have to check DB directly. |
| 52 | Games company signup | Low | Already implemented and working | MVP done. Full flow: status on submissions, staff approve/deny, create company+user, link, notify. Handle duplicates, validate access to existing companies. |
| 53 | Allow members to edit display name, email, and pw | Medium | Settings view is empty placeholder; add form fields | Doesn't block #19 but makes sense to have for member experience. |
| 59 | Set up eShopperReviews as a reviewer | Low | Add ReviewSite entry + feed config | Their data sucks but lots of reviews. Custom scraper needed. One-time scrape to JSON + review import tool. Could reuse for future reviewers! |
| 61 | GH111: more names for games companies | Medium | Need UI for alternative names | Match name variations to one company. Doing some via Claude but UI would help. |
| 62 | New process status: Content does not meet inclusion criteria | Medium | Add new status constant + update logic | Consolidates similar statuses. Needs data fix for existing records. |
| 65 | Game list: by games company | Low | Add repository query + staff view | Surprised it doesn't exist. Quick win. |
| 66 | Submit quick review without signing up | High | Auth system requires user; needs guest flow | Spam risk but need reviews. WordPress-style: Name/Email, cookie, optional auto-account. Low friction. Do even with good signup. |
| 69 | Fix Digitally Downloaded Feedburner review links | Low | Update PartnerFeedLink URL | Older review URLs are dead. Need scraping to find actual URLs. Claude can help. |
| 70 | Re-download hi-res images for N.co.uk linked games | Medium | Add re-download job with filtering | Old images are low quality. Bigger idea: unified crawl queue for images, 404s, missing data. One crawl per game, track last check, space out requests (50-100 every few hours). Manual or periodic re-queue. |
| 87 | GH156: save smaller versions of images for landing pages | High | Requires ImageMagick + CDN strategy | Big images slow pages. But don't want fuzzy images in larger spaces. Balance needed. |
| 90 | GH16: Link to Steam and reviews | High | No Steam integration; requires API | Don't use in ranking but show for games with 0-1 reviews. Better than empty page. |
| 95 | GH76: multiplayer options | High | No field; requires migration + UI | Nintendo has more player info (Local/Online). Related to #107. Worth storing. |

---

## Low Priority

| # | Idea | Complexity | Notes | Your Notes |
|---|------|------------|-------|------------|
| 1 | Staff dashboard: recently added is Switch 1 only | Low | No filtering exists - add console param | Show both consoles together. Space is limited - consider condensed layout. Might show something more valuable instead. |
| 2 | Bulk add tag to games with search string (e.g. Solitaire) | Medium | No bulk tag UI - needs new controller/view | Explore using Claude for mass tagging instead of building UI |
| 13 | Slow queries: stats on Browse by date page | Medium | Heavy stats queries - needs caching/indexes | From pre-Cloudflare logging. May be less urgent now. Could add Redis caching for big queries. Review needed. |
| 16 | Ones to watch: show a list in admin and public | Medium | `one_to_watch` field exists - need views | Manual flag on games. Placement TBD - Members, homepage, or /switch-1/ landing pages. Includes #21. |
| 20 | Move Stats dashboard to Staff dashboard | Low | Stats dashboard exists; reuse queries in staff view | Consolidation - not much on stats dashboard currently. |
| 23 | Split out tag verified into one field per tag category | High | Currently single field; requires ~12 new fields + complex migration | Few games have tags - might re-assess all anyway. Flag tracked update progress. Open to simpler approaches. |
| 32 | Improve 404 page with more useful links | Low | Custom view + related game suggestions | Not much on it currently. Add helpful links to guide users. |
| 33 | Remove old image fields | Low | Used in 4 files; bounded scope migration | Fields: boxart_square_url, boxart_header_image. Validate first. New fields on GameEditorController view. |
| 34 | Change PlayStatus to an Enum | Medium | 7 constants + factory methods; test coverage impact | IDE autocompletion benefit. Code cleanliness. |
| 35 | Change FormatOptions to an Enum | Low | Only 5 format constants; simple extraction | IDE autocompletion benefit. |
| 36 | Change VideoType to an Enum | Low | Only 3 constants in Game model | IDE autocompletion benefit. |
| 37 | Replace sub_str with str_starts_with | Low | 14 usages; straightforward replacement | PHP 8 modernization. |
| 38 | Replace strpos with str_contains | Low | 7 usages; straightforward replacement | PHP 8 modernization. |
| 39 | GH123: Migrate staff pages to Bootstrap 5 | Medium | Staff B5 theme exists; ~20+ views to migrate | Mostly done already. Check for remaining files. |
| 40 | Replace GameRepository->getAll with dropdown search | High | Unbounded query; needs API + dropdown UI | API might use it. API underused - could limit there. Not related to #8 (that's companies). |
| 43 | Add Switch 2 to game search | Low | Console scoping exists; add filter parameter | Needs investigation - likely mixing all games without distinguishing. |
| 46 | Split game list by console | Low | Console filters exist in repository; expose in UI | API ticket - add console filter to API. |
| 47 | Split game list by year | Low | Year searching exists; add grouping in templates | API ticket - add year filter to API. |
| 57 | Caching: user games collection IDs | Low | Cache via Redis/cache layer | Performance optimization. |
| 58 | Show ranked total and % for standard quality games | Medium | GameQualityScore exists; add ranking query | Useful stats - need to decide placement. |
| 63 | Bulk edit: games without store links | Low | Bulk editor exists; add filter | Have tool but one at a time. Claude could help with bulk. |
| 64 | GH41: allow users to select categories they like | Medium | Need new preferences table + UI | Nice if linked to other features. |
| 67 | Remove getUnlinkedDataSourceItem from Api/Game/TitleMatch | Low | Dead code cleanup | Verify unused first. |
| 68 | Roll out table-row-stat to all staff pages | Medium | Template rollout to staff-b5 pages | May have newer Twig macro approach. Same intent. |
| 73 | Related games: one list? (Manual/category/collection/series) | Medium | Unify fragmented related games | 3 sections stacked + manual + S1/S2 editions = too much. Smarter layout needed, not necessarily one box. |
| 74 | Featured games: rotate | Medium | FeaturedGame model exists; add rotation logic | Only in Members (latest 3). Ties to displaying featured elsewhere. May overlap with existing ticket. |
| 75 | Daily stats for monitoring | Medium | No model; create new stats table | Most exist already: games, review links, ranked, without categories, unlinked, N.co.uk parsed. |
| 77 | Video URL: load from N URL? | Medium | Needs new scraper parser | Claude could scrape this. |
| 78 | 404 checker: 100-200 per day, sort by last check/de-listed | Medium | No infrastructure; new model + command | Related to #109 (dead Nintendo URLs). Strategy TBD. |
| 79 | Unranked for members | Low | Add unranked filter to member views | May push members to review games. |
| 80 | Twitter signup / autogenerated email address | High | OAuth exists; needs email auto-gen flow | Tech debt. Low value if removing Twitter. But useful if multiple login methods later. Hold for now. |
| 81 | Review links: remove ability to change site id | Low | Add validation to prevent changes | Only for incorrect imports. Keep for now. |
| 82 | Games collection: hide Format and Hours played when adding; show on edit | Low | UI form logic tweaks | UX help. Newer add-to-collection page exists but not complete yet. |
| 83 | Games collection: set owned date to today, custom date, or ignore | Low | Add date picker with presets | UX help. |
| 84 | Move PartnerUpdateStats > Reviews | Low | Domain folder refactoring | Might be a command - needs checking. |
| 85 | Delete review draft | Low | Add delete endpoint with auth checks | For members. |
| 86 | GH81: use Youtube API to search for videos | High | Requires API key + quota management | Helpful but #77 gives "official" videos. Consider both. |
| 88 | GH141: Generate images for "by collection" like "by series" | High | No image generation service | Series page is long/slow. Collections smaller - more suitable. Fiddly but nice results. |
| 89 | GH56: add more signup methods | Medium | OAuth exists; add providers. *Related to #80* | Maybe just Google. Remove Twitter. Keep email/pw. Risk: multiple accounts if users forget which method. Hard problem. |
| 93 | Raw items - replace with search | Medium | Add search/filter UI with pagination | Maybe kill screen and replace via #14/91/92 combo. Keep ticket, screen may be replaced. |
| 94 | Quick reviews: approve/deny, not Edit | Medium | Change to approve/deny workflow | UX inconsistencies in Staff. |
| 96 | GH124: Allow games companies to update contact details | Medium | Add edit form for companies | Useful but not urgent - few signups, companies inactive. |
| 97 | Show recent quick reviews on homepage/Reviews homepage | Low | Already shown on Community page | Need more reviews first. Already on Members page. |
| 99 | GH30: member profiles | High | No profile model; requires full implementation | Worth doing once more members. Can be lightweight initially. |
| 100 | GH32: Games collection - quick status changes | Medium | Add quick status buttons/AJAX | Have new way but 2 tools exist. Need to consolidate. |
| 101 | Quick reviews: char count in content box | Low | Simple JS addition | Block too many chars. For members page + future guest version. |
| 102 | Onboarding: dismissable notice banner for logged in users | Medium | Need notification model + dismissal | Nice but low priority without new signups. |
| 103 | Upload / edit avatar | High | No avatar field; requires full implementation | Useful as members grow. |
| 105 | Record user id of submitted review links | Low | Add migration + populate records | Tiny, low value. Keep for now. |

---

## Needs Decision

| # | Idea | Complexity | Notes | Your Notes |
|---|------|------------|-------|------------|
| 3 | Change series page to be both consoles | Low | Currently filters by console - remove filter | Part of larger console-split decision. Sparse S2 pages cause "soft 404" SEO issues. Categories might stay split with filter. Needs strategic decision first. Includes #26, #71. |
| 4 | Change collection page to be both consoles | Low | Same pattern as #3 | Part of console-split decision - see #3. Includes #27. |
| 56 | Lists: New additions/DB entries | Low | recentlyAdded() exists; create public list view | Raw DB additions less useful than new releases. Real value might be "surprise releases" - games that appear without being on coming soon. Consider killing or repurposing. |
| 98 | Review sites: link to all reviews by a partner (public) | Low | Add public page listing reviews | Only show latest 20-25 per partner. NLife has 3000+. Rethink or revamp partner profile pages instead? Maybe kill and create new tasks. |
| 107 | Store Local vs Online player counts separately | Medium | Nintendo pages show different counts | Newer data from Nintendo - worth capturing |
| 108 | Scrape Developer from US Nintendo pages | Medium | UK pages don't have Developer, US does | Would need to add US URLs first |
| 109 | Check for dead Nintendo URLs | High | 15k URLs, can't check regularly | Hard-coded URLs may go dead. Need strategy. See #110 for unified approach. |

---

## Merged / Killed / Done

| # | Idea | Status | Notes |
|---|------|--------|-------|
| 21 | Show "one to watch" on site | Merged | → #16 |
| 24 | Rework tag pages for v2 layout | Merged | → #6 |
| 25 | Allow drill down by tag within a category | Merged | → #5 |
| 26 | Update series pages to have Switch 1/2 in single list | Merged | → #3 |
| 27 | Update collection pages to have Switch 1/2 in single list | Merged | → #4 |
| 45 | Games with multiple editions: link together (S1/S2) | Merged | → #44 |
| 48 | Search by games companies without going into the list | Merged | → #8 |
| 54 | Look into splitting low quality games from browse pages | Done | Covered by v2 templates |
| 55 | Send invite codes from requests screen | Superseded | By #19 (open registration) |
| 60 | Auto-assignment rules | Killed | Doing via Claude Code already |
| 71 | View all in series/category: split by Switch 1/2 | Merged | → #3 |
| 72 | Homepage: console split for Recent top rated | Killed | Yearly done, console name shown above images |
| 76 | Bulk edit: publisher link should go to staff | Killed | Low value, may replace bulk edit pages anyway |
| 91 | Data Sources - view parsed item | Merged | → #14 |
| 92 | Link DSParsedItem to DSRawItem | Merged | → #14 |
| 104 | Update sitemaps to include games companies | Killed | 3k thin pages not worth it |

---

## Claude Code Workflow Opportunities

Tasks where Claude Code can help directly (no UI needed):

| Related # | Task |
|-----------|------|
| 2, 18 | Mass tagging games |
| 10, 95 | Scraping Nintendo page data |
| 59 | Review import tool (scrape to JSON) |
| 63 | Bulk affiliate link updates |
| 69 | Finding correct Digitally Downloaded URLs |
| 70, 110 | Crawl queue / backfill operations |
