<?php


namespace App\Services;

use App\Game;


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
    public function getListTopRated()
    {
        $gamesList = Game::where('review_count', '>', '2')
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