# Category, series, collection, tag - new approach

## Summary

We currently have landing and drill-down pages across a few dimensions:

- Category
- Series
- Collection
- Tag

These are split by console.

## Problems

The console split in particular leads to a number of problems:

- A lot of the Switch 2 pages (at least 50%) are empty, and many of the remaining pages have a small number of games.
-- Empty/sparse pages is a UX problem for users, and also causes Google to not index some of those pages.
-- We have mitigated this somewhat by hiding links to the Switch 2 pages where they have 0 games. However, the URLs still exist.
- Every category, series, etc has 2 pages. If we ignore the empty/sparse content issue, pages with a good number of games are split into 2 separate pages.
-- This is more of a problem for Switch 2 owners as all Switch 1 games should run on Switch 2, so the entire library is accessible on Switch 2.
-- If there wasn't a split, it would need to be clear which games are for each console.

## Current design notes

Each of the four page types (category, series, collection, tag) has a landing page and subpages. The four landing pages have different layouts:

- Category is a list showing parent/top-level categories and their children.
- Series is a long page with a generated image based on some of the games in the series. Approx 100-150 series exist, so it's a lot of images to load in one go. But the images do look nice!
- Collection is a simple list. No hierarchy or images.
- Tag is a tabular layout showing tag groups (such as Viewpoint) and tags within each group (such as First-person, Third-person, Side-scroller).

A new v2 design has been applied to category subpages, and these pages have been expanded to include additional filters and a list view. Series, collection, tag subpages are using an older v1 design. The v2 design hasn't been rolled out because we think a new approach may be better to explore first.

## Solutions
The most likely option would be a merged view of each page, with a clear distinction/separation between consoles, or filters/tabs to browse each console separately.

Using the category landing page as an example - the benefit is this becomes a single starting URL, and allows users to browse by each of the four dimensions in one place.

Drawback is it may become much busier for full categories (despite being better for smaller categories). And it also won't scale so well to future consoles - imagine 3 - but this is a longer term option, and shouldn't stop us from proceeding if the option otherwise looks good.

## Links

Category
https://www.switchscores.com/c/switch-1/category
https://www.switchscores.com/c/switch-1/category/adventure
https://www.switchscores.com/c/switch-1/category/adventure/list

Series
https://www.switchscores.com/c/switch-1/series
https://www.switchscores.com/c/switch-1/series/ace-attorney (old design)

Collection
https://www.switchscores.com/c/switch-1/collection
https://www.switchscores.com/c/switch-1/collection/aca-neogeo (old design)

Tag
https://www.switchscores.com/c/switch-1/tag
https://www.switchscores.com/c/switch-1/tag/building (old design)