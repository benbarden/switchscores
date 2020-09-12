<?php


namespace App\Services;

use Illuminate\Support\Facades\DB;

use App\Game;

class GameReleaseDateService
{

    // ********************************************** //
    // Released games
    // ********************************************** //

    /**
     * Welcome page stats
     * @return integer
     */
    public function countReleased()
    {
        return Game::where('games.eu_is_released', 1)->count();
    }

    /**
     * @param int $limit
     * @return mixed
     */
    public function getReleased($limit = null)
    {
        $games = DB::table('games')
            ->select('games.*')
            ->where('games.eu_is_released', 1)
            ->orderBy('games.eu_release_date', 'desc')
            ->orderBy('games.eu_released_on', 'desc')
            ->orderBy('games.updated_at', 'desc')
            ->orderBy('games.title', 'asc');

        if ($limit != null) {
            $games = $games->limit($limit);
        }
        $games = $games->get();

        return $games;
    }

    // ********************************************** //
    // Upcoming games
    // ********************************************** //

    /**
     * This is used on the public site. Games with no release date are hidden.
     * @param int $limit
     * @return mixed
     */
    public function getUpcoming($limit = null)
    {
        $games = DB::table('games')
            ->select('games.*')
            ->where('games.eu_is_released', 0)
            ->whereNotNull('games.eu_release_date')
            ->orderBy('games.eu_release_date', 'asc')
            ->orderBy('games.title', 'asc');

        if ($limit != null) {
            $games = $games->limit($limit);
        }
        $games = $games->get();

        return $games;
    }

    /**
     * @param int $daysLimit
     * @return mixed
     */
    public function getUpcomingSwitchWeekly($daysLimit)
    {
        $games = DB::table('games')
            ->select('games.*')
            ->where('games.eu_is_released', 0)
            ->whereNotNull('games.eu_release_date')
            ->whereRaw('eu_release_date < DATE_ADD(NOW(), INTERVAL ? DAY)', $daysLimit)
            ->orderBy('games.eu_release_date', 'asc')
            ->orderBy('games.title', 'asc');

        $games = $games->get();

        return $games;
    }

    /**
     * Only used for Staff
     * @return integer
     */
    public function countUpcoming()
    {
        return Game::where('games.eu_is_released', 0)->count();
    }

    /**
     * This is used for Staff - all unreleased games are shown.
     * There's no limit (aka 2 Unlimited) as we want the full list.
     * @return mixed
     */
    public function getAllUnreleased()
    {
        $games = DB::table('games')
            ->select('games.*')
            ->where('games.eu_is_released', 0)
            ->whereNotNull('games.eu_release_date')
            ->orderBy('games.eu_release_date', 'asc')
            ->get();

        return $games;
    }

    /**
     * @return mixed
     */
    public function getAllWithNoEuReleaseDate()
    {
        $games = DB::table('games')
            ->select('games.*')
            ->whereNull('games.eu_release_date')
            ->orderBy('games.id', 'desc')
            ->get();

        return $games;
    }

    // ********************************************** //

    /**
     * @param $seriesId
     * @param int $limit
     * @return mixed
     */
    public function getBySeries($seriesId, $limit = null)
    {
        $games = DB::table('games')
            ->select('games.*')
            ->where('games.series_id', $seriesId)
            ->orderBy('games.title', 'asc');

        if ($limit != null) {
            $games = $games->limit($limit);
        }
        $games = $games->get();

        return $games;
    }

    /**
     * @param string $letter
     * @param int $limit
     * @return mixed
     */
    public function getReleasedByLetter($letter, $limit = null)
    {
        $games = Game::where('eu_is_released', 1)
            ->where('title', 'LIKE', $letter.'%')
            ->orderBy('title', 'asc');

        if ($limit != null) {
            $games = $games->limit($limit);
        }
        $games = $games->get();

        return $games;
    }

    /**
     * @param $idList
     * @return mixed
     */
    public function getByIdList($idList)
    {
        $games = DB::table('games')
            ->select('games.*')
            ->whereIn('games.id', $idList)
            ->whereNotNull('games.eu_release_date')
            ->orderBy('games.eu_release_date', 'asc')
            ->orderBy('games.title', 'asc');

        $games = $games->get();

        return $games;
    }

    /**
     * This returns a list of released titles, with ranks, and above a minimum average rating.
     * Sorted to show the newest titles first.
     * @param int $minimumRating
     * @param int $dateInterval
     * @param int $limit
     * @return mixed
     */
    public function getRecentWithGoodRanks($minimumRating = 7, $dateInterval = 30, $limit = 15)
    {
        $games = DB::table('games')
            ->select('games.*')
            ->where('games.eu_is_released', 1)
            ->whereRaw('games.eu_release_date between date_sub(NOW(), INTERVAL ? DAY) and now()', $dateInterval)
            ->whereNotNull('games.game_rank')
            ->where('games.rating_avg', '>', $minimumRating)
            ->orderBy('games.rating_avg', 'desc')
            ->orderBy('games.eu_release_date', 'desc')
            ->orderBy('games.title', 'asc');

        if ($limit != null) {
            $games = $games->limit($limit);
        }
        $games = $games->get();

        return $games;
    }

    /**
     * @param $reviewCount
     * @param $gameIdsReviewedBySite
     * @return mixed
     */
    public function getUnrankedByReviewCount($reviewCount, $gameIdsReviewedBySite)
    {
        $games = DB::table('games')
            ->select('games.*')
            ->where('games.eu_is_released', 1)
            ->where('review_count', '=', $reviewCount)
            ->whereNotIn('games.id', $gameIdsReviewedBySite)
            ->orderBy('games.eu_release_date', 'desc')
            ->orderBy('games.title', 'asc')
            ->get();

        return $games;
    }

    /**
     * @param $year
     * @param $gameIdsReviewedBySite
     * @return mixed
     */
    public function getUnrankedByYear($year, $gameIdsReviewedBySite)
    {
        $games = DB::table('games')
            ->select('games.*')
            ->where('games.eu_is_released', 1)
            ->where('games.release_year', '=', $year)
            ->where('review_count', '<', '3')
            ->whereNotIn('games.id', $gameIdsReviewedBySite)
            ->orderBy('games.eu_release_date', 'desc')
            ->orderBy('games.title', 'asc')
            ->get();

        return $games;
    }

    /**
     * @param $filter
     * @param $gameIdsReviewedBySite
     * @return mixed
     * @throws \Exception
     */
    public function getUnrankedByList($filter, $gameIdsReviewedBySite)
    {
        $games = DB::table('games')
            ->select('games.*')
            ->where('games.eu_is_released', 1)
            ->where('review_count', '<', '3')
            ->whereNotIn('games.id', $gameIdsReviewedBySite);

        switch ($filter) {
            case 'aca-neogeo':
                $games = $games->where('games.title', 'LIKE', 'ACA NeoGeo %');
                break;
            case 'arcade-archives':
                $games = $games->where('games.title', 'LIKE', 'Arcade Archives %');
                break;
            case 'all-others':
                $games = $games->where('games.title', 'NOT LIKE', 'ACA NeoGeo %');
                $games = $games->where('games.title', 'NOT LIKE', 'Arcade Archives %');
                break;
            default:
                throw new \Exception('Unknown filter: '.$filter);
        }

        $games = $games->orderBy('games.eu_release_date', 'desc')
            ->orderBy('games.title', 'asc')
            ->get();

        return $games;
    }
}