<?php
return [

    'groups' => [

        'nintendo_eu' => [
            'label' => 'Nintendo EU ingest',
            'jobs'  => [
                ['cmd' => 'DSNintendoCoUkImportParseLink',    'desc' => 'Import and link data from N.co.uk'],
                ['cmd' => 'DSNintendoCoUkUpdateAvailability', 'desc' => 'Update availability from EU eShop data'],
                ['cmd' => 'DSNintendoCoUkUpdateGames',        'desc' => 'Refresh linked game data'],
                ['cmd' => 'DSNintendoCoUkDownloadPackshots',  'desc' => 'Download packshots'],
            ],
        ],

        'reviews' => [
            'label' => 'Review pipeline',
            'jobs'  => [
                ['cmd' => 'PartnerImportActiveFeeds',     'desc' => 'Fetch partner feeds'],
                ['cmd' => 'ReviewImportByScraper',        'desc' => 'Custom review scraping'],
                ['cmd' => 'PartnerParseReviewDrafts',     'desc' => 'Match drafts to games, parse scores'],
                ['cmd' => 'ReviewConvertDraftsToReviews', 'desc' => 'Generate reviews from drafts'],
                ['cmd' => 'UpdateGameCalendarStats',      'desc' => 'Refresh release calendar stats'],
                ['cmd' => 'ReviewSiteUpdateStats',        'desc' => 'Update partner site review counts/dates'],
                ['cmd' => 'UpdateGameReviewStats',        'desc' => 'Refresh review totals & averages per game'],
                ['cmd' => 'UpdateGameRanks',              'desc' => 'Refresh game rankings'],
            ],
        ],

        'content' => [
            'label' => 'Content',
            'jobs'  => [
                ['cmd' => 'SitemapGenerate',              'desc' => 'Regenerate sitemaps'],
                ['cmd' => 'IGSeriesImage',                'desc' => 'Generate images for series'],
                ['cmd' => 'ReviewCampaignUpdateProgress', 'desc' => 'Update campaign completion progress'],
                ['cmd' => 'RefreshNewsDbUpdateStats',     'desc' => 'Refresh news update stats'],
            ],
        ],

        'quality_integrity' => [
            'label' => 'Quality & integrity',
            'jobs'  => [
                ['cmd' => 'GameUpdateQualityScores',      'desc' => 'Update quality score fields'],
                ['cmd' => 'IntegrityCheckChecker',        'desc' => 'Run integrity checks'],
            ],
        ],

        'ad_hoc' => [
            'label' => 'Ad-hoc',
            'jobs'  => [
                ['cmd' => 'PartnerImportFeed',            'desc' => 'Import content from a single partner feed'],
            ],
        ],

    ],
];