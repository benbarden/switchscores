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

    /**
     * $operator is a GSC dimension filter operator: contains by default, or e.g.
     * excludingRegex, where $contains is the regex.
     */
    public function fetch(
        string $contains,
        int $windowDays,
        int $rowLimit,
        string $operator = 'contains'
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
                            'operator'   => $operator,
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
