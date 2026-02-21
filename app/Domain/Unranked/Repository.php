<?php


namespace App\Domain\Unranked;

use App\Models\Game;

class Repository
{
    public function totalUnranked()
    {
        return Game::whereNull('game_rank')->active()->count();
    }

    public function getForMemberDashboard()
    {
        return Game::where('eu_is_released', 1)
            ->where('is_low_quality', '0')
            ->where('review_count', '2')
            ->active()
            ->where('rating_avg', '>', '7.0')
            ->inRandomOrder()
            ->limit(1)
            ->get();
    }

    public function getByReviewCount($reviewCount, $gameIdsReviewedBySite = null, $limit = null)
    {
        $gameList = Game::where('eu_is_released', 1)
            ->where('is_low_quality', '0')
            ->where('review_count', '=', $reviewCount);

        if ($gameIdsReviewedBySite) {
            $gameList = $gameList->whereNotIn('games.id', $gameIdsReviewedBySite);
        }

        $gameList = $gameList->active()
            ->orderBy('rating_avg', 'desc')
            ->orderBy('eu_release_date', 'desc')
            ->orderBy('title', 'asc');

        if ($limit) {
            $gameList = $gameList->limit($limit);
        }

        return $gameList->get();
    }

    public function totalByReviewCount($reviewCount)
    {
        $gameList = Game::where('eu_is_released', 1)
            ->where('is_low_quality', '0')
            ->where('review_count', '=', $reviewCount)
            ->active();

        return $gameList->count();
    }

    public function getByYear($year, $gameIdsReviewedBySite = null, $limit = null)
    {
        $gameList = Game::where('eu_is_released', 1)
            ->where('is_low_quality', '0')
            ->where('games.release_year', '=', $year)
            ->where('review_count', '<', '3');

        if ($gameIdsReviewedBySite) {
            $gameList = $gameList->whereNotIn('games.id', $gameIdsReviewedBySite);
        }

        $gameList = $gameList->active()
            ->orderBy('rating_avg', 'desc')
            ->orderBy('eu_release_date', 'desc')
            ->orderBy('title', 'asc');

        if ($limit) {
            $gameList = $gameList->limit($limit);
        }

        return $gameList->get();
    }

    public function totalByYear($year)
    {
        $gameList = Game::where('eu_is_released', 1)
            ->where('is_low_quality', '0')
            ->where('games.release_year', '=', $year)
            ->where('review_count', '<', '3')
            ->active();

        return $gameList->count();
    }

    public function getLowQuality()
    {
        $gameList = Game::where('eu_is_released', 1)
            ->where('is_low_quality', '1')
            ->where('review_count', '<', '3')
            ->active()
            ->orderBy('games.eu_release_date', 'desc')
            ->orderBy('games.title', 'asc');

        return $gameList->get();
    }

    public function totalLowQuality()
    {
        $gameList = Game::where('eu_is_released', 1)
            ->where('is_low_quality', '1')
            ->where('review_count', '<', '3')
            ->active();

        return $gameList->count();
    }
}