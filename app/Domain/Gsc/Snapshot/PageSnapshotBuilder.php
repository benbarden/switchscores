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

    /**
     * Page families that are neither site pages nor shown on Insights: one page per tag,
     * series, collection, news article, review site or games company. Matched against the
     * URL path. The bare landing pages (/browse/tag, /news) don't match, so they're site pages.
     */
    const OTHER_PATTERNS = [
        '#^/browse/(tag|series|collection)/#',
        '#^/c/[^/]+/(category|tag|series|collection)/#',
        '#^/news/(\d|category/|database-updates/\d)#',
        '#^/reviews/site/#',
        '#^/partners/(games-company|guides)/#',
    ];

    const GAME_PATTERN = '#^/(switch-2/)?games/\d+(/|$)#';

    /**
     * For the site pages fetch: a GSC excludingRegex (RE2, on the full URL) that leaves out
     * games, Top rated, categories and the other families, so what's left is site pages.
     */
    const SITE_FETCH_EXCLUDE_REGEX = '/(switch-2/)?games/[0-9]+|/top-rated|/browse/(category|tag|series|collection)/'
        .'|/c/[^/]+/(category|tag|series|collection)/|/news/([0-9]|category/|database-updates/[0-9])'
        .'|/reviews/site/|/partners/(games-company|guides)/';

    /**
     * top_rated, game, category (only /browse/category/...), other (see OTHER_PATTERNS), or
     * site for everything else: the homepage, about, the buyer's guide, the console pages
     * (/c/switch-1, its new releases, upcoming and year pages) and the like.
     */
    public function detectPageType(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH) ?: '/';

        if (str_contains($path, '/top-rated')) {
            return 'top_rated';
        }

        if (preg_match(self::GAME_PATTERN, $path)) {
            return 'game';
        }

        if (str_starts_with($path, '/browse/category/')) {
            return 'category';
        }

        foreach (self::OTHER_PATTERNS as $pattern) {
            if (preg_match($pattern, $path)) {
                return 'other';
            }
        }

        return 'site';
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
