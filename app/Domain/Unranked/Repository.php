<?php


namespace App\Domain\Unranked;

use App\Models\Game;

class Repository
{
    public function getForMemberDashboard()
    {
        return Game::where('eu_is_released', 1)
            ->where('is_low_quality', '0')
            ->where('review_count', '2')
            ->where('format_digital', '<>', Game::FORMAT_DELISTED)
            ->where('rating_avg', '>', '7.0')
            ->inRandomOrder()
            ->limit(1)
            ->get();
    }

    public function getByReviewCount($reviewCount, $gameIdsReviewedBySite = null)
    {
        $gameList = Game::where('eu_is_released', 1)
            ->where('is_low_quality', '0')
            ->where('review_count', '=', $reviewCount);

        if ($gameIdsReviewedBySite) {
            $gameList = $gameList->whereNotIn('games.id', $gameIdsReviewedBySite);
        }

        $gameList = $gameList->where('format_digital', '<>', Game::FORMAT_DELISTED)
            ->orderBy('games.eu_release_date', 'desc')
            ->orderBy('games.title', 'asc');

        return $gameList->get();
    }

    public function totalByReviewCount($reviewCount)
    {
        $gameList = Game::where('eu_is_released', 1)
            ->where('is_low_quality', '0')
            ->where('review_count', '=', $reviewCount)
            ->where('format_digital', '<>', Game::FORMAT_DELISTED);

        return $gameList->count();
    }

    public function getByYear($year, $gameIdsReviewedBySite = null)
    {
        $gameList = Game::where('eu_is_released', 1)
            ->where('is_low_quality', '0')
            ->where('games.release_year', '=', $year)
            ->where('review_count', '<', '3');

        if ($gameIdsReviewedBySite) {
            $gameList = $gameList->whereNotIn('games.id', $gameIdsReviewedBySite);
        }

        $gameList = $gameList->where('format_digital', '<>', Game::FORMAT_DELISTED)
            ->orderBy('games.eu_release_date', 'desc')
            ->orderBy('games.title', 'asc');

        return $gameList->get();
    }

    public function totalByYear($year)
    {
        $gameList = Game::where('eu_is_released', 1)
            ->where('is_low_quality', '0')
            ->where('games.release_year', '=', $year)
            ->where('review_count', '<', '3')
            ->where('format_digital', '<>', Game::FORMAT_DELISTED);

        return $gameList->count();
    }

    public function getLowQuality()
    {
        $gameList = Game::where('eu_is_released', 1)
            ->where('is_low_quality', '1')
            ->where('review_count', '<', '3')
            ->where('format_digital', '<>', Game::FORMAT_DELISTED)
            ->orderBy('games.eu_release_date', 'desc')
            ->orderBy('games.title', 'asc');

        return $gameList->get();
    }

    public function totalLowQuality()
    {
        $gameList = Game::where('eu_is_released', 1)
            ->where('is_low_quality', '1')
            ->where('review_count', '<', '3')
            ->where('format_digital', '<>', Game::FORMAT_DELISTED);

        return $gameList->count();
    }
}