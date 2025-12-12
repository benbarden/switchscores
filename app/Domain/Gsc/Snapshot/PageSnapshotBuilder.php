<?php

namespace App\Domain\Gsc\Snapshot;

class PageSnapshotBuilder
{
    public function build(array $rows, bool $isGames = false): array
    {
        $pages = [];

        foreach ($rows as $row) {
            $page   = $row->keys[0];
            $query  = $row->keys[1];
            $clicks = (int) $row->clicks;
            $impr   = (int) $row->impressions;
            $pos    = (float) $row->position;

            $pages[$page] ??= [
                'clicks'      => 0,
                'impressions' => 0,
                'pos_weight'  => 0,
                'queries'     => [],
            ];

            $pages[$page]['clicks']      += $clicks;
            $pages[$page]['impressions'] += $impr;
            $pages[$page]['pos_weight']  += $pos * $impr;

            $pages[$page]['queries'][] = [
                'query'  => $query,
                'clicks' => $clicks,
            ];
        }

        foreach ($pages as $page => &$data) {
            $data['avg_position'] = $data['impressions'] > 0
                ? round($data['pos_weight'] / $data['impressions'], 2)
                : null;

            usort(
                $data['queries'],
                fn ($a, $b) => $b['clicks'] <=> $a['clicks']
            );

            $data['top_queries'] = array_column(
                array_slice($data['queries'], 0, 5),
                'query'
            );

            $data['query_count'] = count($data['queries']);
            $data['page_type']   = $this->detectPageType($page);
            $data['game_id'] = $this->extractGameId($page);

            unset($data['queries'], $data['pos_weight']);
        }

        if ($isGames) {
            $pages = array_filter(
                $pages,
                fn ($d) => $this->keepGamePage($d)
            );
        }

        return $pages;
    }

    protected function detectPageType(string $url): string
    {
        if (str_contains($url, '/top-rated')) {
            return 'top_rated';
        }

        if (str_contains($url, '/games/')) {
            return 'game';
        }

        if (str_contains($url, '/c/')) {
            return 'category';
        }

        return 'other';
    }

    protected function keepGamePage(array $data): bool
    {
        return
            $data['impressions'] >= 100 ||
            $data['clicks'] >= 3 ||
            ($data['avg_position'] !== null && $data['avg_position'] <= 15);
    }

    protected function extractGameId(string $url): ?int
    {
        if (preg_match('#/games/(\d+)/#', $url, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }
}
