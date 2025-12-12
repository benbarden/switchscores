<?php

namespace App\Domain\Gsc\Snapshot;

use Google\Client;
use Google\Service\SearchConsole;

class GscClient
{
    public function makeService(): SearchConsole
    {
        $client = new Client();

        $client->setAuthConfig(
            storage_path('app/google/service-account.json')
        );

        $client->setScopes([
            'https://www.googleapis.com/auth/webmasters.readonly',
        ]);

        return new SearchConsole($client);
    }
}
