<?php


namespace App\Services;

use App\GameReleaseDate;
use Illuminate\Support\Facades\DB;


class GameReleaseDateService
{
    public function createGameReleaseDate(
        $gameId, $region, $releaseDate, $released, $upcomingDate
    )
    {
        $isReleased = $released == 'on' ? 1 : 0;

        $releaseYear = $this->getReleaseYear($releaseDate);

        GameReleaseDate::create([
            'game_id' => $gameId,
            'region' => $region,
            'release_date' => $releaseDate,
            'is_released' => $isReleased,
            'upcoming_date' => $upcomingDate,
            'release_year' => $releaseYear
        ]);
    }

    public function editGameReleaseDate(
        GameReleaseDate $gameReleaseDate, $releaseDate, $released, $upcomingDate
    )
    {
        $isReleased = $released == 'on' ? 1 : 0;

        $releaseYear = $this->getReleaseYear($releaseDate);

        $values = [
            'release_date' => $releaseDate,
            'is_released' => $isReleased,
            'upcoming_date' => $upcomingDate,
            'release_year' => $releaseYear
        ];

        $gameReleaseDate->fill($values);
        $gameReleaseDate->save();
    }

    public function deleteByGameId($gameId)
    {
        GameReleaseDate::where('game_id', $gameId)->delete();
    }

    // ********************************************************** //

    /**
     * @param $releaseDate
     * @throws \Exception
     * @return null|string
     */
    public function getReleaseYear($releaseDate)
    {
        if ($releaseDate) {
            $releaseDateObject = new \DateTime($releaseDate);
            $releaseYear = $releaseDateObject->format('Y');
        } else {
            $releaseYear = null;
        }

        return $releaseYear;
    }

    public function getByGame($gameId)
    {
        return GameReleaseDate::where('game_id', $gameId)->get();
    }

    public function getByGameAndRegion($gameId, $region)
    {
        $releaseDate = GameReleaseDate::
            where('game_id', $gameId)
            ->where('region', $region)
            ->get();

        if (!$releaseDate->isEmpty()) {
            return $releaseDate->first();
        } else {
            return null;
        }
    }

    // ********************************************** //

    /**
     * Welcome page stats
     * @return integer
     */
    public function countReleased()
    {
        $gameCount = DB::table('games')
            ->where('games.eu_is_released', 1)
            ->count();

        return $gameCount;
    }

    /**
     * Welcome page stats
     * @return integer
     */
    public function countUpcoming()
    {
        $gameCount = DB::table('games')
            ->where('games.eu_is_released', 0)
            ->count();

        return $gameCount;
    }

    // ********************************************** //

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

    /**
     * @param $primaryTypeId
     * @param int $limit
     * @return mixed
     */
    public function getReleasedByPrimaryType($primaryTypeId, $limit = null)
    {
        $games = DB::table('games')
            ->select('games.*')
            ->where('games.eu_is_released', 1)
            ->where('games.primary_type_id', $primaryTypeId)
            ->orderBy('games.rating_avg', 'desc')
            ->orderBy('games.title', 'asc');

        if ($limit != null) {
            $games = $games->limit($limit);
        }
        $games = $games->get();

        return $games;
    }

    /**
     * @param $seriesId
     * @param $region
     * @param int $limit
     * @return mixed
     */
    public function getReleasedBySeries($seriesId, $limit = null)
    {
        $games = DB::table('games')
            ->select('games.*')
            ->where('games.eu_is_released', 1)
            ->where('games.series_id', $seriesId)
            ->orderBy('games.rating_avg', 'desc')
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
        $games = DB::table('games')
            ->select('games.*')
            ->where('games.eu_is_released', 1)
            ->where('games.title', 'LIKE', $letter.'%')
            ->orderBy('games.title', 'asc');

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
     * @return mixed
     */
    public function getUnreleased()
    {
        $games = DB::table('games')
            ->select('games.*')
            ->where('games.eu_is_released', 0)
            ->whereNull('games.eu_release_date')
            ->orderBy('games.title', 'asc');

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