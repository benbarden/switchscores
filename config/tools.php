<?php

/**
 * Tools Hub commands that never run nightly. The nightly jobs come from
 * config/cron-groups.php, one tab per group; these are the hub's last tab.
 * Only commands that run without arguments belong here - the hub has nowhere to enter one.
 */
return [

    'ad_hoc' => [
        'label' => 'Ad-hoc',
        'steps' => [
            'game:crawl-batch --limit=10' => 'Crawl Nintendo URLs for 404s (10 games)',
        ],
    ],

];
