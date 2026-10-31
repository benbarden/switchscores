<?php

namespace App\Domain\AffiliateCodes;

use App\Enums\AmazonAffiliateStatus;
use App\Models\Console;
use App\Models\Game;
use App\Models\GscPageSnapshot;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Games on the Top rated pages that haven't been checked for a direct Amazon link, per region.
 * Those pages carry buy buttons to far more search visitors than game pages do.
 */
class TopRatedGaps
{
    public const ALL_TIME = 'all-time';

    public const REGIONS = ['us', 'uk'];

    private const TOP_RANKS = 100;

    /**
     * Gap counts for the Affiliates page: all-time first, then each year, newest first.
     *
     * @return array<int, array{label: string, scope: string, counts: array<int, int>}>
     */
    public function overview(string $region): array
    {
        $rows = [[
            'label' => 'All-time',
            'scope' => self::ALL_TIME,
            'counts' => $this->countsByConsole($region, self::ALL_TIME),
        ]];

        foreach ($this->years() as $year) {
            $rows[] = [
                'label' => (string) $year,
                'scope' => (string) $year,
                'counts' => $this->countsByConsole($region, (string) $year),
            ];
        }

        return $rows;
    }

    /**
     * Checked games out of every game on a Top rated page, per console. A game on several pages
     * counts once, so the total is lower than the sum of the overview's lists.
     *
     * @return array<int, array{done: int, total: int, percentage: int}>
     */
    public function progress(string $region): array
    {
        $topGames = DB::table('game_rank_alltime')
            ->select('game_id')
            ->where('game_rank', '<=', self::TOP_RANKS)
            ->union(
                DB::table('game_rank_year')->select('game_id')->where('game_rank', '<=', self::TOP_RANKS)
            );

        $query = fn () => Game::query()->joinSub($topGames, 'top_games', 'top_games.game_id', '=', 'games.id');

        $totals = $this->countByConsole($query());
        $gaps = $this->countByConsole($this->applyGapFilter($query(), $region));

        $progress = [];
        foreach ($totals as $consoleId => $total) {
            $done = $total - ($gaps[$consoleId] ?? 0);
            $progress[$consoleId] = [
                'done' => $done,
                'total' => $total,
                // Rounded down, so 100% means finished.
                'percentage' => (int) floor($done / $total * 100),
            ];
        }

        return $progress;
    }

    public function isValidScope(string $scope): bool
    {
        return $scope === self::ALL_TIME || in_array((int) $scope, $this->years(), true);
    }

    /**
     * @return Collection<int, Game> With the game's rank on that page in page_rank.
     */
    public function games(string $region, int $consoleId, string $scope): Collection
    {
        $rankTable = $this->rankTable($scope);

        return $this->gapQuery($region, $scope)
            ->where('games.console_id', $consoleId)
            ->select('games.*')
            ->addSelect($rankTable.'.game_rank as page_rank')
            ->orderBy($rankTable.'.game_rank')
            ->orderBy('games.title')
            ->get();
    }

    public function pagePath(Console $console, string $scope): string
    {
        $base = '/c/'.$console->slug.'/top-rated/';

        return $base.($scope === self::ALL_TIME ? self::ALL_TIME : 'by-year/'.$scope);
    }

    /**
     * Search clicks from the latest snapshot, or null if the snapshot doesn't have the page.
     *
     * @return array{clicks: int|null, windowDays: int|null, snapshotDate: string|null}
     */
    public function pageStats(string $pagePath): array
    {
        $none = ['clicks' => null, 'windowDays' => null, 'snapshotDate' => null];

        $latest = GscPageSnapshot::where('page_type', 'top_rated')->max('snapshot_date');
        if (!$latest) {
            return $none;
        }

        $snapshots = GscPageSnapshot::where('page_type', 'top_rated')
            ->whereDate('snapshot_date', $latest)
            ->get();

        foreach ($snapshots as $snapshot) {
            if (parse_url($snapshot->page_url, PHP_URL_PATH) === $pagePath) {
                return [
                    'clicks' => (int) $snapshot->clicks,
                    'windowDays' => (int) $snapshot->window_days,
                    'snapshotDate' => $snapshot->snapshot_date->format('Y-m-d'),
                ];
            }
        }

        return $none;
    }

    private function countsByConsole(string $region, string $scope): array
    {
        return $this->countByConsole($this->gapQuery($region, $scope));
    }

    private function countByConsole(Builder $query): array
    {
        return $query
            ->toBase()
            ->selectRaw('games.console_id, COUNT(*) AS games_count')
            ->groupBy('games.console_id')
            ->pluck('games_count', 'console_id')
            ->map(fn ($count) => (int) $count)
            ->all();
    }

    private function years(): array
    {
        return DB::table('game_rank_year')
            ->distinct()
            ->orderByDesc('release_year')
            ->pluck('release_year')
            ->map(fn ($year) => (int) $year)
            ->all();
    }

    private function rankTable(string $scope): string
    {
        return $scope === self::ALL_TIME ? 'game_rank_alltime' : 'game_rank_year';
    }

    private function gapQuery(string $region, string $scope): Builder
    {
        $rankTable = $this->rankTable($scope);

        $query = Game::query()
            ->join($rankTable, $rankTable.'.game_id', '=', 'games.id')
            ->where($rankTable.'.game_rank', '<=', self::TOP_RANKS);

        if ($scope !== self::ALL_TIME) {
            $query->where($rankTable.'.release_year', (int) $scope);
        }

        return $this->applyGapFilter($query, $region);
    }

    private function applyGapFilter(Builder $query, string $region): Builder
    {
        $prefix = 'games.amazon_'.($region === 'uk' ? 'uk' : 'us').'_';

        return $query
            ->where($prefix.'status', AmazonAffiliateStatus::UNCHECKED->value)
            ->where(fn ($q) => $q->whereNull($prefix.'asin')->orWhere($prefix.'asin', ''))
            ->where(fn ($q) => $q->whereNull($prefix.'link')->orWhere($prefix.'link', ''));
    }
}
