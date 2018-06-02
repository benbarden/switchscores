<?php


namespace App\Services;

use App\Game;
use App\GameGenre;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class GameService
{
    const ORDER_TITLE = 0;
    const ORDER_NEWEST = 1;
    const ORDER_OLDEST = 2;

    public function create(
        $title, $linkTitle, $priceEshop, $players,
        $developer, $publisher, $amazonUkLink, $overview,
        $mediaFolder, $videoUrl
    )
    {
        return Game::create([
            'title' => $title,
            'link_title' => $linkTitle,
            'price_eshop' => $priceEshop,
            'players' => $players,
            'overview' => $overview,
            'developer' => $developer,
            'publisher' => $publisher,
            'media_folder' => $mediaFolder,
            'review_count' => 0,
            'amazon_uk_link' => $amazonUkLink,
            'video_url' => $videoUrl,
        ]);
    }

    public function edit(
        Game $game,
        $title, $linkTitle, $priceEshop, $players,
        $developer, $publisher, $amazonUkLink, $overview,
        $mediaFolder, $videoUrl
    )
    {
        $values = [
            'title' => $title,
            'link_title' => $linkTitle,
            'price_eshop' => $priceEshop,
            'players' => $players,
            'overview' => $overview,
            'developer' => $developer,
            'publisher' => $publisher,
            'media_folder' => $mediaFolder,
            'amazon_uk_link' => $amazonUkLink,
            'video_url' => $videoUrl,
        ];

        $game->fill($values);
        $game->save();
    }

    // ********************************************************** //

    public function find($id)
    {
        return Game::find($id);
    }

    public function getByTitle($title)
    {
        $game = Game::where('title', $title)
            ->first();
        return $game;
    }

    public function getAll($region)
    {
        $games = DB::table('games')
            ->join('game_release_dates', 'games.id', '=', 'game_release_dates.game_id')
            ->select('games.*',
                'game_release_dates.release_date',
                'game_release_dates.is_released',
                'game_release_dates.upcoming_date',
                'game_release_dates.release_year')
            ->where('game_release_dates.region', $region)
            ->orderBy('games.title', 'asc');
        $games = $games->get();

        return $games;
    }

    public function getWithoutDevOrPub()
    {
        $gamesList = Game::where('developer', null)->orWhere('publisher', null)->orderBy('id', 'asc')->get();
        return $gamesList;
    }

    public function getWithoutAmazonUkLink()
    {
        $gamesList = Game::where('amazon_uk_link', null)->orderBy('id', 'desc')->get();
        return $gamesList;
    }

    public function getWithoutVideoUrl()
    {
        $gamesList = Game::where('video_url', null)->orderBy('id', 'desc')->get();
        return $gamesList;
    }
}