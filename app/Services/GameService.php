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

    /**
     * @param $title
     * @param $linkTitle
     * @param $priceEshop
     * @param $players
     * @param $developer
     * @param $publisher
     * @param null $amazonUkLink
     * @param null $overview
     * @param null $mediaFolder
     * @param null $videoUrl
     * @param null $boxartUrl
     * @param null $boxartSquareUrl
     * @param null $vendorPageUrl
     * @param null $nintendoPageUrl
     * @param null $twitterId
     * @return Game
     */
    public function create(
        $title, $linkTitle, $priceEshop, $players, $developer, $publisher,
        $amazonUkLink = null, $overview = null, $mediaFolder = null, $videoUrl = null,
        $boxartUrl = null, $boxartSquareUrl = null, $vendorPageUrl = null, $nintendoPageUrl = null, $twitterId = null
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
            'boxart_url' => $boxartUrl,
            'boxart_square_url' => $boxartSquareUrl,
            'vendor_page_url' => $vendorPageUrl,
            'nintendo_page_url' => $nintendoPageUrl,
            'twitter_id' => $twitterId,
        ]);
    }

    public function edit(
        Game $game,
        $title, $linkTitle, $priceEshop, $players, $developer, $publisher,
        $amazonUkLink = null, $overview = null, $mediaFolder = null, $videoUrl = null,
        $boxartUrl = null, $boxartSquareUrl = null, $vendorPageUrl = null, $nintendoPageUrl = null, $twitterId = null
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
            'boxart_url' => $boxartUrl,
            'boxart_square_url' => $boxartSquareUrl,
            'vendor_page_url' => $vendorPageUrl,
            'nintendo_page_url' => $nintendoPageUrl,
            'twitter_id' => $twitterId,
        ];

        $game->fill($values);
        $game->save();
    }

    public function deleteGame($gameId)
    {
        Game::where('id', $gameId)->delete();
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

    public function getWithoutBoxart()
    {
        $gamesList = Game::where('boxart_url', null)->where('boxart_square_url', null)->orderBy('id', 'asc')->get();
        return $gamesList;
    }

    public function getWithoutAmazonUkLink()
    {
        $gamesList = Game::where('amazon_uk_link', null)->orderBy('id', 'asc')->get();
        return $gamesList;
    }

    public function getByNullField($field)
    {
        $allowedFields = [
            'video_url', 'vendor_page_url', 'nintendo_page_url', 'twitter_id'
        ];

        if (!in_array($field, $allowedFields)) {
            throw new \Exception('Field '.$field.' not supported by getByMissingField');
        }

        $gamesList = Game::where($field, null)->orderBy('id', 'asc')->get();
        return $gamesList;
    }

    public function getNextId($filter, $currentId)
    {
        $gameList = null;

        $regionCode = \Request::get('regionCode');

        switch ($filter) {
            //case 'no-genre':
            //    $gameList = $serviceGameGenre->getGamesWithoutGenres($regionCode);
            //    break;
            case 'no-dev-or-pub':
                $gameList = $this->getWithoutDevOrPub();
                break;
            case 'no-boxart':
                $gameList = $this->getWithoutBoxart();
                break;
            case 'no-video-url':
                $gameList = $this->getByNullField('video_url');
                break;
            case 'no-vendor-page-url':
                $gameList = $this->getByNullField('vendor_page_url');
                break;
            case 'no-nintendo-page-url':
                $gameList = $this->getByNullField('nintendo_page_url');
                break;
            case 'no-twitter-id':
                $gameList = $this->getByNullField('twitter_id');
                break;
            case 'no-amazon-uk-link':
                $gameList = $this->getWithoutAmazonUkLink();
                break;
            default:
                return null;
                break;
        }

        //$this_id = 123;
        //$id_array = array('349','430','123','423','113');

        if ($gameList == null) return null;

        $gameIdList = $gameList->pluck('id')->toArray();

        if (count($gameIdList) == 0) return null;

        $flipped = array_flip($gameIdList);

        if (count($flipped) == 0) return null;

        if (!array_key_exists($currentId, $flipped)) return null;

        $nextId = $gameIdList[$flipped[$currentId]+1];

        return $nextId;
    }
}