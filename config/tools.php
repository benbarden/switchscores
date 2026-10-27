<?php

/**
 * Tools Hub commands that never run nightly (#214). The nightly jobs come from
 * config/cron-groups.php, one tab per group; these are the hub's last tab.
 */
return [

    'ad_hoc' => [
        'label' => 'Ad-hoc',
        'steps' => [
            'PartnerImportFeed' => 'Import content from a single partner feed',
            'game:crawl-batch --limit=10' => 'Crawl Nintendo URLs for 404s (10 games)',
        ],
    ],

];
