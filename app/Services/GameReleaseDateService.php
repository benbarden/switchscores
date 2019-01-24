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

    public function markAsReleased(GameReleaseDate $gameReleaseDate)
    {
        $gameReleaseDate->is_released = 1;
        $gameReleaseDate->save();
    }

    // ********************************************************** //

    /**
     * @param $releaseDate
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
     * @param $region
     * @return integer
     */
    public function countReleased($region)
    {
        $gameCount = DB::table('games')
            ->join('game_release_dates', 'games.id', '=', 'game_release_dates.game_id')
            ->where('game_release_dates.region', $region)
            ->where('game_release_dates.is_released', 1)
            ->count();

        return $gameCount;
    }

    /**
     * Welcome page stats
     * @param $region
     * @return integer
     */
    public function countUpcoming($region)
    {
        $gameCount = DB::table('games')
            ->join('game_release_dates', 'games.id', '=', 'game_release_dates.game_id')
            ->where('game_release_dates.region', $region)
            ->where('game_release_dates.is_released', 0)
            ->where('upcoming_date', 'not like', 'Unreleased')
            ->count();

        return $gameCount;
    }

    // ********************************************** //

    /**
     * @param $region
     * @param int $limit
     * @return mixed
     */
    public function getReleased($region, $limit = null)
    {
        $games = DB::table('games')
            ->join('game_release_dates', 'games.id', '=', 'game_release_dates.game_id')
            ->select('games.*',
                'game_release_dates.release_date',
                'game_release_dates.is_released',
                'game_release_dates.upcoming_date',
                'game_release_dates.release_year')
            ->where('game_release_dates.region', $region)
            ->where('game_release_dates.is_released', 1)
            ->orderBy('game_release_dates.release_date', 'desc')
            ->orderBy('games.title', 'asc');

        if ($limit != null) {
            $games = $games->limit($limit);
        }
        $games = $games->get();

        return $games;
    }

    /**
     * @param $idList
     * @param $region
     * @return mixed
     */
    public function getByIdList($idList, $region)
    {
        $games = DB::table('games')
            ->join('game_release_dates', 'games.id', '=', 'game_release_dates.game_id')
            ->select('games.*',
                'game_release_dates.release_date',
                'game_release_dates.is_released',
                'game_release_dates.upcoming_date',
                'game_release_dates.release_year')
            ->where('game_release_dates.region', $region)
            ->whereIn('games.id', $idList)
            ->orderBy('game_release_dates.upcoming_date', 'asc')
            ->orderBy('games.title', 'asc');

        $games = $games->get();

        return $games;
    }

    /**
     * @param $region
     * @param int $limit
     * @return mixed
     */
    public function getUpcoming($region, $limit = null)
    {
        $games = DB::table('games')
            ->join('game_release_dates', 'games.id', '=', 'game_release_dates.game_id')
            ->select('games.*',
                'game_release_dates.release_date',
                'game_release_dates.is_released',
                'game_release_dates.upcoming_date',
                'game_release_dates.release_year')
            ->where('game_release_dates.region', $region)
            ->where('game_release_dates.is_released', 0)
            ->where('upcoming_date', 'not like', 'Unreleased');
            //->whereNotNull('game_release_dates.release_date')

        if ($limit != null) {
            // We usually limit this list to show a shortened version on a top-level landing page.
            // Those pages also format the dates. So, we need to make sure we have a
            // real release date, or the upcoming date won't format correctly.
            $games = $games->whereNotNull('game_release_dates.release_date');
        }

        $games = $games->orderBy('game_release_dates.upcoming_date', 'asc')
            ->orderBy('games.title', 'asc');

        if ($limit != null) {
            $games = $games->limit($limit);
        }
        $games = $games->get();

        return $games;
    }

    /**
     * @param $mode
     * @param $year
     * @param $region
     * @throws \Exception
     * @return \Illuminate\Support\Collection
     */
    public function getUpcomingYearByMode($mode, $year, $region)
    {
        $year = (int) $year;
        if (!$year) throw new \Exception('No year specified');

        switch ($mode) {
            case 'year-with-dates':
                $dbLike = $year.'-%';
                break;
            case 'year-quarters':
                $dbLike = $year.'-Q%';
                break;
            case 'year-xx':
                $dbLike = $year.'%-XX';
                break;
            default:
                throw new \Exception('Unknown mode: '.$mode);
                break;
        }

        $games = DB::table('games')
            ->join('game_release_dates', 'games.id', '=', 'game_release_dates.game_id')
            ->select('games.*',
                'game_release_dates.release_date',
                'game_release_dates.is_released',
                'game_release_dates.upcoming_date',
                'game_release_dates.release_year')
            ->where('game_release_dates.region', $region)
            ->where('game_release_dates.is_released', 0);

        if ($mode == 'year-with-dates') {
            $games = $games->whereNotNull('game_release_dates.release_date');
        }

        $games = $games->where('game_release_dates.upcoming_date', 'like', $dbLike)
            ->orderBy('game_release_dates.upcoming_date', 'asc')
            ->orderBy('games.title', 'asc');

        $games = $games->get();

        return $games;
    }

    /**
     * @param $year
     * @param $region
     * @return \Illuminate\Support\Collection
     */
    public function getUpcomingYearWithDates($year, $region)
    {
        return $this->getUpcomingYearByMode('year-with-dates', $year, $region);
    }

    /**
     * @param $year
     * @param $region
     * @return \Illuminate\Support\Collection
     */
    public function getUpcomingYearQuarters($year, $region)
    {
        return $this->getUpcomingYearByMode('year-quarters', $year, $region);
    }

    /**
     * @param $year
     * @param $region
     * @return \Illuminate\Support\Collection
     */
    public function getUpcomingYearXs($year, $region)
    {
        return $this->getUpcomingYearByMode('year-xx', $year, $region);
    }

    /**
     * @param $region
     * @return mixed
     */
    public function getUpcomingTBA($region)
    {
        $games = DB::table('games')
            ->join('game_release_dates', 'games.id', '=', 'game_release_dates.game_id')
            ->select('games.*',
                'game_release_dates.release_date',
                'game_release_dates.is_released',
                'game_release_dates.upcoming_date',
                'game_release_dates.release_year')
            ->where('game_release_dates.region', $region)
            ->where('game_release_dates.is_released', 0)
            ->where('game_release_dates.upcoming_date', 'TBA')
            ->orderBy('game_release_dates.upcoming_date', 'asc')
            ->orderBy('games.title', 'asc');

        $games = $games->get();

        return $games;
    }

    /**
     * @param $region
     * @return mixed
     */
    public function getUnreleased($region)
    {
        $games = DB::table('games')
            ->join('game_release_dates', 'games.id', '=', 'game_release_dates.game_id')
            ->select('games.*',
                'game_release_dates.release_date',
                'game_release_dates.is_released',
                'game_release_dates.upcoming_date',
                'game_release_dates.release_year')
            ->where('game_release_dates.region', $region)
            ->where('game_release_dates.is_released', 0)
            ->where('game_release_dates.upcoming_date', 'Unreleased')
            ->orderBy('game_release_dates.upcoming_date', 'asc')
            ->orderBy('games.title', 'asc');

        $games = $games->get();

        return $games;
    }

    /**
     * @param $currentYear
     * @param $region
     * @return \Illuminate\Support\Collection
     * @throws \Exception
     */
    public function getUpcomingFuture($currentYear, $region)
    {
        $currentYear = (int) $currentYear;
        if (!$currentYear) throw new \Exception('No year specified');

        $games = DB::table('games')
            ->join('game_release_dates', 'games.id', '=', 'game_release_dates.game_id')
            ->select('games.*',
                'game_release_dates.release_date',
                'game_release_dates.is_released',
                'game_release_dates.upcoming_date',
                'game_release_dates.release_year')
            ->where('game_release_dates.region', $region)
            ->where('game_release_dates.is_released', 0)
            ->where('upcoming_date', 'not like', $currentYear.'%')
            ->where('upcoming_date', 'not like', '2017%') // Temporary workaround
            ->where('upcoming_date', 'not like', 'TBA')
            ->where('upcoming_date', 'not like', 'Unreleased')
            ->orderBy('game_release_dates.upcoming_date', 'asc')
            ->orderBy('games.title', 'asc');

        $games = $games->get();

        return $games;
    }

    /**
     * @param $region
     * @return mixed
     */
    public function getReviewsNeeded($region)
    {
        $games = DB::table('games')
            ->join('game_release_dates', 'games.id', '=', 'game_release_dates.game_id')
            ->select('games.*',
                'game_release_dates.release_date',
                'game_release_dates.is_released',
                'game_release_dates.upcoming_date',
                'game_release_dates.release_year')
            ->where('game_release_dates.region', $region)
            ->where('game_release_dates.is_released', 1)
            ->where('review_count', '<', '3')
            ->orderBy('game_release_dates.release_date', 'desc')
            ->orderBy('games.title', 'asc')
            ->get();

        return $games;
    }

    /**
     * @param $reviewCount
     * @param $region
     * @return mixed
     */
    public function getUnrankedByReviewCount($reviewCount, $region)
    {
        $games = DB::table('games')
            ->join('game_release_dates', 'games.id', '=', 'game_release_dates.game_id')
            ->select('games.*',
                'game_release_dates.release_date',
                'game_release_dates.is_released',
                'game_release_dates.upcoming_date',
                'game_release_dates.release_year')
            ->where('game_release_dates.region', $region)
            ->where('game_release_dates.is_released', 1)
            ->where('review_count', '=', $reviewCount)
            ->orderBy('game_release_dates.release_date', 'desc')
            ->orderBy('games.title', 'asc')
            ->get();

        return $games;
    }

    /**
     * @param $year
     * @param $region
     * @return mixed
     */
    public function getUnrankedByYear($year, $region)
    {
        $games = DB::table('games')
            ->join('game_release_dates', 'games.id', '=', 'game_release_dates.game_id')
            ->select('games.*',
                'game_release_dates.release_date',
                'game_release_dates.is_released',
                'game_release_dates.upcoming_date',
                'game_release_dates.release_year')
            ->where('game_release_dates.region', $region)
            ->where('game_release_dates.is_released', 1)
            ->where('game_release_dates.release_year', '=', $year)
            ->where('review_count', '<', '3')
            ->orderBy('game_release_dates.release_date', 'desc')
            ->orderBy('games.title', 'asc')
            ->get();

        return $games;
    }

    /**
     * @param $filter
     * @param $region
     * @return mixed
     * @throws \Exception
     */
    public function getUnrankedByList($filter, $region)
    {
        $games = DB::table('games')
            ->join('game_release_dates', 'games.id', '=', 'game_release_dates.game_id')
            ->select('games.*',
                'game_release_dates.release_date',
                'game_release_dates.is_released',
                'game_release_dates.upcoming_date',
                'game_release_dates.release_year')
            ->where('game_release_dates.region', $region)
            ->where('game_release_dates.is_released', 1)
            ->where('review_count', '<', '3');

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

        $games = $games->orderBy('game_release_dates.release_date', 'desc')
            ->orderBy('games.title', 'asc')
            ->get();

        return $games;
    }
}