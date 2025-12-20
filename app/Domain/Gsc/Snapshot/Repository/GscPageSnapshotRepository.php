<?php

namespace App\Domain\Gsc\Snapshot\Repository;

use App\Models\GscPageSnapshot;
use Illuminate\Database\Eloquent\Collection;

class GscPageSnapshotRepository
{
    public function latestSnapshotForPageType(
        string $pageType,
        int $windowDays = 28
    ): array {
        $latestDate = GscPageSnapshot::where('page_type', $pageType)
            ->where('window_days', $windowDays)
            ->max('snapshot_date');

        if (!$latestDate) {
            return [
                'snapshot_date' => null,
                'window_days'   => $windowDays,
                'rows'          => [],
            ];
        }

        return [
            'snapshot_date' => $latestDate,
            'window_days'   => $windowDays,
            'rows' => GscPageSnapshot::where('page_type', $pageType)
                ->where('window_days', $windowDays)
                ->where('snapshot_date', $latestDate)
                ->orderByDesc('impressions')
                ->get(),
        ];
    }

    public function latestGamesSnapshot(
        int $windowDays = 28,
        int $minImpressions = 5
    ): array {
        $latestDate = GscPageSnapshot::where('page_type', 'game')
            ->where('window_days', $windowDays)
            ->max('snapshot_date');

        if (!$latestDate) {
            return [
                'snapshot_date' => null,
                'window_days'   => $windowDays,
                'rows'          => collect(),
            ];
        }

        return [
            'snapshot_date' => $latestDate,
            'window_days'   => $windowDays,
            'rows' => GscPageSnapshot::where('page_type', 'game')
                ->where('window_days', $windowDays)
                ->where('snapshot_date', $latestDate)
                ->where('impressions', '>=', $minImpressions)
                ->orderByDesc('impressions')
                ->get(),
        ];
    }

    public function getSnapshotsByGame(int $gameId, int $limit = null): Collection
    {
        $data = GscPageSnapshot::query();

        $data = $data->where('game_id', $gameId)
            ->orderBy('id', 'desc');

        if ($limit) {
            $data = $data->limit($limit);
        }

        return $data->get();
    }

}