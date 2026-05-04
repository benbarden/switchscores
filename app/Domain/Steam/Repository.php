<?php

namespace App\Domain\Steam;

use App\Enums\SteamStatus;
use App\Models\Game;
use App\Models\SteamReviewData;

class Repository
{
    public function countByStatus(SteamStatus $status): int
    {
        return Game::where('steam_status', $status->value)
            ->where('is_low_quality', 0)
            ->active()
            ->count();
    }

    public function getLinkedGames()
    {
        return Game::where('steam_status', SteamStatus::LINKED->value)
            ->active()
            ->get(['id', 'title', 'steam_id']);
    }

    public function getReviewDataForGame(int $gameId): ?SteamReviewData
    {
        return SteamReviewData::where('game_id', $gameId)->first();
    }

    public function upsertReviewData(int $gameId, string $steamId, array $summary): bool
    {
        $incoming = [
            'review_score'      => $summary['review_score'] ?? null,
            'review_score_desc' => $summary['review_score_desc'] ?? null,
            'total_positive'    => $summary['total_positive'] ?? 0,
            'total_negative'    => $summary['total_negative'] ?? 0,
            'total_reviews'     => $summary['total_reviews'] ?? 0,
        ];

        $existing = SteamReviewData::where('game_id', $gameId)->first();

        $changed = !$existing
            || (int) $existing->review_score      !== (int) $incoming['review_score']
            || $existing->review_score_desc        !== $incoming['review_score_desc']
            || (int) $existing->total_positive    !== (int) $incoming['total_positive']
            || (int) $existing->total_negative    !== (int) $incoming['total_negative']
            || (int) $existing->total_reviews     !== (int) $incoming['total_reviews'];

        SteamReviewData::updateOrCreate(
            ['game_id' => $gameId],
            array_merge($incoming, ['steam_id' => $steamId, 'last_synced_at' => now()])
        );

        return $changed;
    }

    public function getUnrankedNotChecked(?int $limit = null)
    {
        $query = Game::where('steam_status', SteamStatus::NOT_CHECKED->value)
            ->where('is_low_quality', 0)
            ->whereNull('game_rank')
            ->active()
            ->orderBy('review_count', 'asc')
            ->orderBy('eu_release_date', 'asc');

        if ($limit) {
            $query = $query->limit($limit);
        }

        return $query->get();
    }

    public function countUnrankedNotChecked(): int
    {
        return Game::where('steam_status', SteamStatus::NOT_CHECKED->value)
            ->where('is_low_quality', 0)
            ->whereNull('game_rank')
            ->active()
            ->count();
    }

    public function getByStatus(SteamStatus $status, ?int $limit = null, bool $oldestFirst = false)
    {
        $dateOrder = $oldestFirst ? 'asc' : 'desc';

        $query = Game::where('steam_status', $status->value)
            ->where('is_low_quality', 0)
            ->active()
            ->orderBy('eu_release_date', $dateOrder)
            ->orderBy('title', 'asc');

        if ($status === SteamStatus::LINKED) {
            $query = $query->with('steamReviewData');
        }

        if ($limit) {
            $query = $query->limit($limit);
        }

        return $query->get();
    }
}
