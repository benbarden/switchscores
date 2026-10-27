<?php

/**
 * Nightly job groups (#185). Each group runs its steps in order through cron:group, which
 * records every step on its own in job_runs. scripts/switchscores-cron schedules the groups,
 * each in its own 30-minute slot; cron health reads the steps from here.
 *
 * Adding a job: add a line to the right group. A failed step doesn't stop the ones after it.
 */
return [

    // Steam reviews - 50 games (the command's default), never-synced first, then oldest-synced (~2 min)
    'steam' => [
        'game:sync-steam-reviews',
    ],

    // Nintendo.co.uk API: the raw import, then per-SKU prices from the price API (via the
    // relay). FetchPrices stores before UpdateGames reads them (#162, #183). Switch 1 is ~5 min
    // (9k NSUIDs).
    'nintendo-import' => [
        'DSNintendoCoUkImportParseLink',
        'DSNintendoCoUkFetchPrices --console=2',
        'DSNintendoCoUkFetchPrices --console=1',
    ],

    // UpdateGames sets each game's price from its standalone SKU (Switch 2, and Switch 1 since
    // #132) and falls back to the search payload's price. ApplyPrices is a dry-run report per
    // console: "changed" should be 0 each night, and anything else shows as a warning on the
    // staff home.
    'nintendo-process' => [
        'DSNintendoCoUkUpdateAvailability',
        'DSNintendoCoUkUpdateGames',
        'DSNintendoCoUkDownloadPackshots',
        'DSNintendoCoUkApplyPrices --console=2',
        'DSNintendoCoUkApplyPrices --console=1',
    ],

    // Title match rates come after the feed import so the night's new drafts are in the sample
    'reviews-import' => [
        'PartnerImportActiveFeeds',
        'ReviewImportByScraper',
        'PartnerUpdateTitleMatchRates',
    ],

    'reviews-drafts' => [
        'PartnerParseReviewDrafts',
        'ReviewConvertDraftsToReviews',
        'ReviewSiteUpdateStats',
        'ReviewCampaignUpdateProgress',
    ],

    // After the drafts are converted, so the night's new reviews count
    'games' => [
        'UpdateGameReviewStats',
        'UpdateGameRanks',
        'UpdateGameCalendarStats',
        'UpdateGameQualityScores',
    ],

    // Site content, stats and checks (the Watchlist, #184, will go here)
    'site' => [
        'SitemapGenerate',
        'IGSeriesImage',
        'RefreshNewsDbUpdateStats',
        'IntegrityCheckChecker',
        'app:fetch-gsc-page-snapshots',
    ],

    // Check Nintendo URLs for 404s - 100 games a night (~5.5 min)
    'crawl' => [
        'game:crawl-batch --limit=100 --delay=3',
    ],

];
