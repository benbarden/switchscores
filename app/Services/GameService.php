<?php


namespace App\Services;

use App\Game;
use Carbon\Carbon;


class GameService
{
    const ORDER_TITLE = 0;
    const ORDER_NEWEST = 1;
    const ORDER_OLDEST = 2;

    public function create(
        $title, $linkTitle, $releaseDate, $priceEshop, $players, $upcoming, $upcomingDate,
        $overview, $developer, $publisher
    )
    {
        $isUpcoming = $upcoming == 'on' ? 1 : 0;

        Game::create([
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
        ]);
    }

    public function edit(
        Game $game, $title, $linkTitle, $releaseDate, $priceEshop, $players, $upcoming, $upcomingDate,
        $overview, $developer, $publisher
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
    public function getListTopRated()
    {
        $gamesList = Game::where('review_count', '>', '2')
            ->orderBy('rating_avg', 'desc')
            ->get();
        return $gamesList;
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
        $gamesList = Game::where('upcoming', 1)->where('upcoming_date', 'TBA')->orderBy('upcoming_date', 'asc')->get();
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
     * Used for Admin - Game filters
     * @return mixed
     */
    public function getWithoutDevOrPub()
    {
        $gamesList = Game::where('developer', null)->orWhere('publisher', null)->orderBy('upcoming_date', 'asc')->get();
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
            ->orderBy('title', 'asc')
            ->get();

        return $gameList;
    }
}