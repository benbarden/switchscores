<?php

/**
 * Nightly job groups (#185). Each group runs its steps in order through cron:group, which
 * records every step on its own in job_runs. scripts/switchscores-cron schedules the groups,
 * each in its own 30-minute slot; cron health reads the steps from here.
 *
 * The Tools Hub shows one tab per group, with the label and each step's description (#214),
 * so this is the one list of jobs for both. Commands that never run nightly are in
 * config/tools.php.
 *
 * Adding a job: add a line to the right group's steps. A failed step doesn't stop the ones
 * after it.
 */
return [

    // Steam reviews - 50 games (the command's default), never-attempted first, then oldest-attempted (~2 min)
    'steam' => [
        'label' => 'Steam reviews',
        'steps' => [
            'game:sync-steam-reviews' => 'Sync Steam review summaries for 50 linked games',
        ],
    ],

    // Nintendo.co.uk API: the raw import, then per-SKU prices from the price API (via the
    // relay). FetchPrices stores before UpdateGames reads them (#162, #183). Switch 1 is ~5 min
    // (9k NSUIDs).
    'nintendo-import' => [
        'label' => 'Nintendo import',
        'steps' => [
            'DSNintendoCoUkImportParseLink' => 'Import and link data from Nintendo.co.uk',
            'DSNintendoCoUkFetchPrices --console=2' => 'Fetch per-SKU prices from the price API (Switch 2)',
            'DSNintendoCoUkFetchPrices --console=1' => 'Fetch per-SKU prices from the price API (Switch 1)',
        ],
    ],

    // UpdateGames sets each game's price from its standalone SKU (Switch 2, and Switch 1 since
    // #132) and falls back to the search payload's price. ApplyPrices is a dry-run report per
    // console: "changed" should be 0 each night, and anything else shows as a warning on the
    // staff home.
    'nintendo-process' => [
        'label' => 'Nintendo process',
        'steps' => [
            'DSNintendoCoUkUpdateAvailability' => 'Update availability from the eShop data',
            'DSNintendoCoUkUpdateGames' => 'Refresh linked game data, including prices',
            'DSNintendoCoUkDownloadPackshots' => 'Download packshots',
            'DSNintendoCoUkApplyPrices --console=2' => 'Price report, dry run: prices that differ from the price API (Switch 2)',
            'DSNintendoCoUkApplyPrices --console=1' => 'Price report, dry run: prices that differ from the price API (Switch 1)',
        ],
    ],

    // Title match rates come after the feed import so the night's new drafts are in the sample
    'reviews-import' => [
        'label' => 'Reviews import',
        'steps' => [
            'PartnerImportActiveFeeds' => 'Fetch partner feeds',
            'ReviewImportByScraper' => 'Custom review scraping',
            'PartnerUpdateTitleMatchRates' => 'Update each feed\'s title match rate',
        ],
    ],

    'reviews-drafts' => [
        'label' => 'Review drafts',
        'steps' => [
            'PartnerParseReviewDrafts' => 'Match drafts to games, parse scores',
            'ReviewConvertDraftsToReviews' => 'Generate reviews from drafts',
            'ReviewSiteUpdateStats' => 'Update partner site review counts and dates',
            'ReviewCampaignUpdateProgress' => 'Update campaign completion progress',
        ],
    ],

    // After the drafts are converted, so the night's new reviews count
    'games' => [
        'label' => 'Games',
        'steps' => [
            'UpdateGameReviewStats' => 'Refresh review totals and averages per game',
            'UpdateGameRanks' => 'Refresh game rankings',
            'UpdateGameCalendarStats' => 'Refresh release calendar stats',
            'UpdateGameQualityScores' => 'Update quality score fields',
        ],
    ],

    // Site content, stats and checks (the Watchlist, #184, will go here)
    'site' => [
        'label' => 'Site',
        'steps' => [
            'SitemapGenerate' => 'Regenerate sitemaps',
            'IGSeriesImage' => 'Generate images for series',
            'RefreshNewsDbUpdateStats' => 'Refresh news update stats',
            'IntegrityCheckChecker' => 'Run integrity checks',
            'app:fetch-gsc-page-snapshots' => 'Fetch Google Search Console page snapshots',
        ],
    ],

    // Check Nintendo URLs for 404s - 100 games a night (~5.5 min)
    'crawl' => [
        'label' => 'Crawl',
        'steps' => [
            'game:crawl-batch --limit=100 --delay=3' => 'Crawl Nintendo URLs for 404s (100 games)',
        ],
    ],

];
