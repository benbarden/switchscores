<?php


namespace App\Services;

use App\Game;


class GameService
{
    const ORDER_TITLE = 0;
    const ORDER_NEWEST = 1;
    const ORDER_OLDEST = 2;

    public function create(
        $title, $linkTitle, $releaseDate, $priceEshop, $players, $upcoming, $upcomingDate, $overview
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
        ]);
    }

    public function edit(
        Game $game, $title, $linkTitle, $releaseDate, $priceEshop, $players, $upcoming, $upcomingDate, $overview
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

    public function getAllReleased()
    {
        $gamesList = Game::where('upcoming', 0)->orderBy('title', 'asc')->get();
        return $gamesList;
    }

    public function getAllUpcoming()
    {
        $gamesList = Game::where('upcoming', 1)->orderBy('upcoming_date', 'asc')->get();
        return $gamesList;
    }

    public function getAllUpcomingTBA()
    {
        $gamesList = Game::where('upcoming', 1)->where('upcoming_date', 'TBA')->orderBy('upcoming_date', 'asc')->get();
        return $gamesList;
    }

    public function getAllUpcomingQs()
    {
        $gamesList = Game::where('upcoming', 1)->where('upcoming_date', 'like', '%Q%')->orderBy('upcoming_date', 'asc')->get();
        return $gamesList;
    }

    public function getAllUpcomingXs()
    {
        $gamesList = Game::where('upcoming', 1)->where('upcoming_date', 'like', '%-XX')->orderBy('upcoming_date', 'asc')->get();
        return $gamesList;
    }
}