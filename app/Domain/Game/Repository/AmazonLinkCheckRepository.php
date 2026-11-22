<?php

namespace App\Domain\Game\Repository;

use App\Enums\AmazonAffiliateStatus;
use App\Enums\AmazonLinkCheckResult;
use App\Models\Game;
use Illuminate\Database\Eloquent\Collection;

/**
 * The nightly Amazon US link check's results on games: what it checks, saving what it found, and
 * the games with a problem for staff to look at.
 */
class AmazonLinkCheckRepository
{
    /**
     * Linked US games with an ASIN: what the nightly link check looks at.
     */
    public function linkedUsWithAsin(): Collection
    {
        return $this->linkedUs()
            ->orderBy('id')
            ->get(['id', 'title', 'console_id', 'amazon_us_asin']);
    }

    /**
     * Saves the link check's results, one update per result. A bulk update, so it adds no audit
     * rows and leaves updated_at alone: the check runs nightly over every linked game, and a
     * check isn't an edit.
     *
     * @param array<string, int[]> $gameIdsByResult game IDs keyed by AmazonLinkCheckResult value
     */
    public function saveUsCheckResults(array $gameIdsByResult, \DateTimeInterface $checkedAt): void
    {
        Game::withoutTimestamps(function () use ($gameIdsByResult, $checkedAt) {
            foreach ($gameIdsByResult as $result => $gameIds) {
                foreach (array_chunk($gameIds, 500) as $chunk) {
                    Game::query()->whereIn('id', $chunk)->update([
                        'amazon_us_check_result' => $result,
                        'amazon_us_checked_at' => $checkedAt,
                    ]);
                }
            }
        });
    }

    /**
     * Linked US games whose last check found a problem, best ranked first. Includes the ones
     * marked as fine; the summary sets those aside.
     */
    public function usProblems(): Collection
    {
        return $this->linkedUs()
            ->whereNotNull('amazon_us_check_result')
            ->where('amazon_us_check_result', '<>', AmazonLinkCheckResult::OK->value)
            ->orderByRaw('game_rank IS NULL')
            ->orderBy('game_rank')
            ->orderBy('id')
            ->get();
    }

    /**
     * When the check last ran, from the latest result saved. Null if it never has.
     */
    public function lastUsCheckedAt(): ?string
    {
        return $this->linkedUs()->max('amazon_us_checked_at');
    }

    /**
     * Marks the game's current US link as checked by hand and fine, so it leaves the list. A new
     * ASIN brings it back if the check finds a problem with that one.
     */
    public function markUsLinkOk(Game $game): void
    {
        $game->amazon_us_check_ok_asin = $game->amazon_us_asin;
        $game->save();
    }

    public function undoUsLinkOk(Game $game): void
    {
        $game->amazon_us_check_ok_asin = null;
        $game->save();
    }

    private function linkedUs()
    {
        return Game::query()
            ->where('amazon_us_status', AmazonAffiliateStatus::LINKED->value)
            ->whereNotNull('amazon_us_asin')
            ->where('amazon_us_asin', '<>', '');
    }
}
