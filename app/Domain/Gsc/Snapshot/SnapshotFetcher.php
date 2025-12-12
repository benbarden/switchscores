<?php

namespace App\Domain\Gsc\Snapshot;

use Google\Service\SearchConsole;
use Google\Service\SearchConsole\SearchAnalyticsQueryRequest;

class SnapshotFetcher
{
    public function __construct(
        protected SearchConsole $service,
        protected string $siteUrl
    ) {}

    public function fetch(
        string $contains,
        int $windowDays,
        int $rowLimit
    ): array {
        $request = new SearchAnalyticsQueryRequest([
            'startDate' => now()->subDays($windowDays + 2)->toDateString(),
            'endDate'   => now()->subDays(2)->toDateString(),
            'dimensions' => ['page', 'query'],
            'dimensionFilterGroups' => [
                [
                    'filters' => [
                        [
                            'dimension'  => 'page',
                            'operator'   => 'contains',
                            'expression' => $contains,
                        ],
                    ],
                ],
            ],
            'rowLimit' => $rowLimit,
        ]);

        $response = $this->service->searchanalytics->query(
            $this->siteUrl,
            $request
        );

        return $response->getRows() ?? [];
    }
}
