<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Google\Client;
use Google\Service\SearchConsole;
class GoogleSearchConsoleTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:google-search-console-test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $client = new Client();

        $client->setAuthConfig(storage_path('app/google/service-account.json'));
        $client->setScopes([
            'https://www.googleapis.com/auth/webmasters.readonly',
        ]);

        $service = new SearchConsole($client);

        $siteUrl = 'https://www.switchscores.com/';

        $request = new SearchConsole\SearchAnalyticsQueryRequest([
            'startDate'  => now()->subDays(30)->toDateString(),
            'endDate'    => now()->subDays(2)->toDateString(),
            'dimensions' => ['page', 'query'],
            'dimensionFilterGroups' => [
                [
                    'filters' => [
                        [
                            'dimension'  => 'page',
                            'operator'   => 'contains',
                            'expression' => '/games/'
                        ]
                    ]
                ]
            ],
            'rowLimit' => 50,
        ]);

        $response = $service->searchanalytics->query($siteUrl, $request);

// Dump raw response for now
        dd($response->getRows());
    }
}
