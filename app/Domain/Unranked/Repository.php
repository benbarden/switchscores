<?php


namespace App\Domain\Unranked;

use App\Models\Game;

class Repository
{
    public function getByReviewCount($reviewCount, $gameIdsReviewedBySite)
    {
        return Game::where('eu_is_released', 1)
            ->where('review_count', '=', $reviewCount)
            ->whereNotIn('games.id', $gameIdsReviewedBySite)
            ->where('format_digital', '<>', Game::FORMAT_DELISTED)
            ->orderBy('games.eu_release_date', 'desc')
            ->orderBy('games.title', 'asc')
            ->get();
    }

    public function getByYear($year, $gameIdsReviewedBySite)
    {
        return Game::where('games.eu_is_released', 1)
            ->where('games.release_year', '=', $year)
            ->where('review_count', '<', '3')
            ->whereNotIn('games.id', $gameIdsReviewedBySite)
            ->where('format_digital', '<>', Game::FORMAT_DELISTED)
            ->orderBy('games.eu_release_date', 'desc')
            ->orderBy('games.title', 'asc')
            ->get();
    }
}