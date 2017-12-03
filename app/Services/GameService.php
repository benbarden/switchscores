<?php


namespace App\Services;

use App\Game;
use App\GameGenre;
use Carbon\Carbon;


class GameService
{
    const ORDER_TITLE = 0;
    const ORDER_NEWEST = 1;
    const ORDER_OLDEST = 2;

    public function create(
        $title, $linkTitle, $releaseDate, $priceEshop, $players, $upcoming, $upcomingDate,
        $overview, $developer, $publisher, $amazonUkLink, $videoUrl
    )
    {
        $isUpcoming = $upcoming == 'on' ? 1 : 0;

        return Game::create([
            'title' => $title,
            'link_title' => $linkTitle,
            'release_date' => $releaseDate,
            'price_eshop' => $priceEshop,
            'players' => $players,
            'upcoming' => $isUpcoming,
            'upcoming_date' => $upcomingDate,
            'overview' => $overview,
            'publisher' => $publisher,
            'developer' => $developer,
            'review_count' => 0,
            'amazon_uk_link' => $amazonUkLink,
            'video_url' => $videoUrl,
        ]);
    }

    public function edit(
        Game $game, $title, $linkTitle, $releaseDate, $priceEshop, $players, $upcoming, $upcomingDate,
        $overview, $developer, $publisher, $amazonUkLink, $videoUrl
    )
    {
        $isUpcoming = $upcoming == 'on' ? 1 : 0;

        $values = [
            'title' => $title,
            'link_title' => $linkTitle,
            'release_date' => $releaseDate,
            'price_eshop' => $priceEshop,
            'players' => $players,
            'upcoming' => $isUpcoming,
            'upcoming_date' => $upcomingDate,
            'overview' => $overview,
            'publisher' => $publisher,
            'developer' => $developer,
            'amazon_uk_link' => $amazonUkLink,
            'video_url' => $videoUrl,
        ];

        $game->fill($values);
        $game->save();
    }

    public function updateReviewStats(
        Game $game, $reviewCount, $ratingAvg
    )
    {
        $values = [
            'review_count' => $reviewCount,
            'rating_avg' => $ratingAvg,
        ];

        $game->fill($values);
        $game->save();

    }

    // ********************************************************** //

    public function find($id)
    {
        return Game::find($id);
    }

    public function getAll($orderBy = self::ORDER_TITLE)
    {
        switch ($orderBy) {
            case self::ORDER_NEWEST:
                $orderField = 'id';
                $orderDir = 'desc';
                break;
            case self::ORDER_OLDEST:
                $orderField = 'id';
                $orderDir = 'asc';
                break;
            case self::ORDER_TITLE:
            default:
                $orderField = 'title';
                $orderDir = 'asc';
                break;
        }
        $gamesList = Game::orderBy($orderField, $orderDir)->get();
        return $gamesList;
    }

    /**
     * Used for public game lists
     * @return mixed
     */
    public function getListReleased()
    {
        $gamesList = Game::where('upcoming', 0)
            ->orderBy('release_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();
        return $gamesList;
    }

    /**
     * Used for public game lists
     * @return mixed
     */
    public function getListReleasedByMonth($month)
    {
        $gamesList = Game::where('upcoming', 0)
            ->whereMonth('release_date', '=', $month)
            ->orderBy('release_date', 'asc')
            ->orderBy('title', 'asc')
            ->get();
        return $gamesList;
    }

    /**
     * @return mixed
     */
    public function getListReleasedLastXDays($days, $limit = 10)
    {
        $days = (int) $days;

        // Note - Had to add a day or the current day won't show up
        // It seemed to work here without that extra day, but not in upcoming
        // Adding here just in case.
        $gamesList = Game::where('upcoming', 0)
            ->whereBetween('release_date', array(Carbon::now()->subDays($days), Carbon::now()->addDay()))
            ->orderBy('release_date', 'desc')
            ->orderBy('title', 'asc')
            ->limit($limit)
            ->get();
        return $gamesList;
    }

    /**
     * @return mixed
     */
    public function getListUpcomingNextXDays($days, $limit = 10)
    {
        $days = (int) $days;

        // Note - Had to sub a day or the current day won't show up
        $gamesList = Game::where('upcoming', 1)
            ->whereBetween('release_date', array(Carbon::now()->subDay(), Carbon::now()->addDays($days)))
            ->orderBy('release_date', 'asc')
            ->orderBy('title', 'asc')
            ->limit($limit)
            ->get();
        return $gamesList;
    }

    /**
     * Used for public game lists
     * @return mixed
     */
    public function getListUpcoming()
    {
        $gamesList = Game::where('upcoming', 1)
            ->orderBy('upcoming_date', 'asc')
            ->orderBy('title', 'asc')
            ->get();
        return $gamesList;
    }

    /**
     * Used for public game lists
     * @return mixed
     */
    public function getListUpcomingByMonth($month)
    {
        $gamesList = Game::where('upcoming', 1)
            ->whereMonth('release_date', '=', $month)
            ->orderBy('release_date', 'asc')
            ->orderBy('title', 'asc')
            ->get();
        return $gamesList;
    }

    /**
     * Used for public game lists
     * Top Rated - All-time
     * @return mixed
     */
    public function getListTopRated($limit = null)
    {
        if ($limit == null) {
            $gamesList = Game::where('review_count', '>', '2')
                ->orderBy('rating_avg', 'desc')
                ->get();
        } else {
            $gamesList = Game::where('review_count', '>', '2')
                ->orderBy('rating_avg', 'desc')
                ->orderBy('review_count', 'desc')
                ->limit($limit)
                ->get();
        }
        return $gamesList;
    }

    /**
     * Top Rated - All-time
     * Just a counter. Used on Game pages
     * @return integer
     */
    public function getListTopRatedCount()
    {
        $topRatedCounter = Game::where('review_count', '>', '2')
            ->orderBy('rating_avg', 'desc')
            ->get()->count();
        return $topRatedCounter;
    }

    /**
     * Top Rated - Last X days
     * @param integer $days
     * @param integer $limit
     * @return mixed
     */
    public function getListTopRatedLastXDays($days, $limit = 10)
    {
        $days = (int) $days;

        $gamesList = Game::where('review_count', '>', '2')
            ->whereBetween('release_date', array(Carbon::now()->subDays($days), Carbon::now()->addDay()))
            ->orderBy('rating_avg', 'desc')
            ->limit($limit)
            ->get();
        return $gamesList;
    }

    /**
     * Top Rated - By month
     * @param integer $month
     * @return mixed
     */
    public function getListTopRatedByMonth($month)
    {
        $gamesList = Game::where('review_count', '>', '2')
            ->whereMonth('release_date', '=', $month)
            ->orderBy('rating_avg', 'desc')
            ->get();
        return $gamesList;
    }

    /**
     * Used for public game lists
     * @return mixed
     */
    public function getListReviewsNeeded()
    {
        $gamesList = Game::where('upcoming', 0)
            ->where('review_count', '<', '3')
            ->orderBy('release_date', 'desc')
            ->get();
        return $gamesList;
    }

    /**
     * Used for Admin - Game filters
     * @return mixed
     */
    public function getAllReleased()
    {
        $gamesList = Game::where('upcoming', 0)->orderBy('title', 'asc')->get();
        return $gamesList;
    }

    /**
     * Used for Admin - Game filters
     * @return mixed
     */
    public function getAllUpcoming()
    {
        $gamesList = Game::where('upcoming', 1)->orderBy('upcoming_date', 'asc')->get();
        return $gamesList;
    }

    /**
     * Used for Admin - Game filters
     * @return mixed
     */
    public function getAllUpcomingTBA()
    {
        $gamesList = Game::where('upcoming', 1)
            ->where('upcoming_date', 'TBA')
            ->orderBy('title', 'asc')
            ->orderBy('upcoming_date', 'asc')
            ->get();
        return $gamesList;
    }

    /**
     * Used for Admin - Game filters
     * @return mixed
     */
    public function getAllUpcomingQs()
    {
        $gamesList = Game::where('upcoming', 1)->where('upcoming_date', 'like', '%Q%')->orderBy('upcoming_date', 'asc')->get();
        return $gamesList;
    }

    /**
     * Used for Admin - Game filters
     * @return mixed
     */
    public function getAllUpcomingXs()
    {
        $gamesList = Game::where('upcoming', 1)->where('upcoming_date', 'like', '%-XX')->orderBy('upcoming_date', 'asc')->get();
        return $gamesList;
    }

    /**
     * Used for Upcoming Games
     * @param $year
     * @return \Illuminate\Support\Collection
     * @throws \Exception
     */
    public function getAllUpcomingYearWithDates($year)
    {
        $year = (int) $year;
        if (!$year) throw new \Exception('No year specified');
        $gamesList = Game::where('upcoming', 1)
            ->whereNotNull('release_date')
            ->where('upcoming_date', 'like', $year.'-%')
            ->orderBy('upcoming_date', 'asc')
            ->orderBy('title', 'asc')
            ->get();
        return $gamesList;
    }

    /**
     * Used for Upcoming Games
     * @param $year
     * @return \Illuminate\Support\Collection
     * @throws \Exception
     */
    public function getAllUpcomingYearQuarters($year)
    {
        $year = (int) $year;
        if (!$year) throw new \Exception('No year specified');
        $gamesList = Game::where('upcoming', 1)
            ->where('upcoming_date', 'like', $year.'-Q%')
            ->orderBy('upcoming_date', 'asc')
            ->orderBy('title', 'asc')
            ->get();
        return $gamesList;
    }

    /**
     * Used for Upcoming Games
     * @param $year
     * @return \Illuminate\Support\Collection
     * @throws \Exception
     */
    public function getAllUpcomingYearXs($year)
    {
        $year = (int) $year;
        if (!$year) throw new \Exception('No year specified');
        $gamesList = Game::where('upcoming', 1)
            ->where('upcoming_date', 'like', $year.'%-XX')
            ->orderBy('upcoming_date', 'asc')
            ->orderBy('title', 'asc')
            ->get();
        return $gamesList;
    }

    /**
     * Used for Upcoming Games
     * @param $currentYear
     * @return \Illuminate\Support\Collection
     * @throws \Exception
     */
    public function getAllUpcomingFuture($currentYear)
    {
        $currentYear = (int) $currentYear;
        if (!$currentYear) throw new \Exception('No year specified');

        $gamesList = Game::where('upcoming', 1)
            //->whereNull('release_date')
            ->where('upcoming_date', 'not like', $currentYear.'%')
            ->where('upcoming_date', 'not like', '2017%') // Temporary workaround
            ->where('upcoming_date', 'not like', 'TBA')
            ->orderBy('upcoming_date', 'asc')
            ->orderBy('title', 'asc')
            ->get();
        return $gamesList;
    }

    /**
     * Used for Admin - Game filters
     * @return mixed
     */
    public function getWithoutDevOrPub()
    {
        $gamesList = Game::where('developer', null)->orWhere('publisher', null)->orderBy('upcoming_date', 'asc')->get();
        return $gamesList;
    }

    /**
     * Used for Admin - Game filters
     * @return mixed
     */
    public function getWithoutAmazonUkLink()
    {
        $gamesList = Game::where('amazon_uk_link', null)->orderBy('id', 'desc')->get();
        return $gamesList;
    }

    /**
     * Used for genre list pages
     * @return mixed
     */
    public function getGamesByGenre($genreId)
    {
        $gameList = Game::whereHas('gameGenres', function($query) use ($genreId) {
            $query->where('genre_id', '=', $genreId);
        })
            ->where('upcoming', '0')
            ->orderBy('title', 'asc')
            ->get();

        return $gameList;
    }

    /**
     * Used for Admin - Game filters
     * @return mixed
     */
    public function getGamesWithoutGenres()
    {
        $gameIds = GameGenre::pluck('game_id')->all();
        $gamesList = Game::whereNotIn('id', $gameIds)->where('upcoming', 0)->get();
        return $gamesList;
    }
}