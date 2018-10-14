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
            ->where('game_release_dates.region', '=', $region)
            ->orderBy('games.title', 'asc');
        $games = $games->get();

        return $games;
    }

    public function getAllWithoutEshopId($region)
    {
        if ($region == 'eu') {
            $field = 'eshop_europe_fs_id';
        } else {
            throw new \Exception('Unsupported region: '.$region);
        }

        $games = Game::whereNull('games.'.$field)
            ->orderBy('games.title', 'asc')
            ->get();

        return $games;
    }

    private function getListLikeText($region, $likeText)
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
            ->where('games.title', 'LIKE', $likeText)
            ->orderBy('games.title', 'asc');
        $games = $games->get();

        return $games;
    }

    public function getListAcaNeoGeo($region)
    {
        return $this->getListLikeText($region, 'ACA NeoGeo %');
    }

    public function getListArcadeArchives($region)
    {
        return $this->getListLikeText($region, 'Arcade Archives %');
    }

    public function getListGolfGames($region)
    {
        return $this->getListLikeText($region, '%golf%');
    }

    public function getListLegoGames($region)
    {
        return $this->getListLikeText($region, 'Lego %');
    }

    public function getListPinballGames($region)
    {
        return $this->getListLikeText($region, '%pinball%');
    }

    public function getListMahjongGames($region)
    {
        return $this->getListLikeText($region, '%mahjong%');
    }

    public function getByDeveloper($region, $developer)
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
            ->where('games.developer', $developer)
            ->orderBy('games.title', 'asc');
        $games = $games->get();

        return $games;
    }

    public function getByPublisher($region, $publisher)
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
            ->where('games.publisher', $publisher)
            ->orderBy('games.title', 'asc');
        $games = $games->get();

        return $games;
    }

    public function getByIdList($idList)
    {
        $gamesList = Game::whereIn('id', $idList)->orderBy('title', 'asc')->get();
        return $gamesList;
    }

    /**
     * @param $region
     * @param int $limit
     * @return mixed
     */
    public function getActionListGamesForRelease($region, $limit = null)
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
            ->whereRaw('DATE(game_release_dates.release_date) <= CURDATE()');

        $games = $games->orderBy('game_release_dates.release_date', 'asc')
            ->orderBy('games.title', 'asc');

        if ($limit != null) {
            $games = $games->limit($limit);
        }
        $games = $games->get();

        return $games;
    }

    /**
     * @param $region
     * @param int $limit
     * @return mixed
     */
    public function getActionListRecentNoNintendoUrl($region, $limit = null)
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
            ->whereNull('games.nintendo_page_url');

        $games = $games->orderBy('game_release_dates.release_date', 'asc')
            ->orderBy('games.title', 'asc');

        if ($limit != null) {
            $games = $games->limit($limit);
        }
        $games = $games->get();

        return $games;
    }

    /**
     * @param $region
     * @param int $limit
     * @return mixed
     */
    public function getActionListUpcomingNoNintendoUrl($region, $limit = null)
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
            ->whereNull('games.nintendo_page_url')
            ->whereNotNull('game_release_dates.release_date');

        $games = $games->orderBy('game_release_dates.release_date', 'asc')
            ->orderBy('games.title', 'asc');

        if ($limit != null) {
            $games = $games->limit($limit);
        }
        $games = $games->get();

        return $games;
    }

    /**
     * @param $region
     * @param int $limit
     * @return mixed
     */
    public function getActionListNintendoUrlNoPackshots($region, $limit = null)
    {
        $games = DB::table('games')
            ->join('game_release_dates', 'games.id', '=', 'game_release_dates.game_id')
            ->select('games.*',
                'game_release_dates.release_date',
                'game_release_dates.is_released',
                'game_release_dates.upcoming_date',
                'game_release_dates.release_year')
            ->where('game_release_dates.region', $region)
            ->whereNotNull('games.nintendo_page_url')
            ->whereNull('boxart_url')
            ->whereNull('boxart_square_url');

        $games = $games->orderBy('game_release_dates.release_date', 'asc')
            ->orderBy('games.title', 'asc');

        if ($limit != null) {
            $games = $games->limit($limit);
        }
        $games = $games->get();

        return $games;
    }

    public function getWithoutDevOrPub()
    {
        $gamesList = Game::where('developer', null)->orWhere('publisher', null)->orderBy('id', 'asc')->get();
        return $gamesList;
    }

    public function getWithoutBoxart($region)
    {
        $gamesList = DB::table('games')
            ->join('game_release_dates', 'games.id', '=', 'game_release_dates.game_id')
            ->select('games.*',
                'game_release_dates.release_date',
                'game_release_dates.is_released',
                'game_release_dates.upcoming_date',
                'game_release_dates.release_year')
            ->where('game_release_dates.region', $region)
            ->where('boxart_url', null)
            ->where('boxart_square_url', null)
            ->orderBy('game_release_dates.release_date', 'asc')
            //->orderBy('game_release_dates.upcoming_date', 'asc')
            ->orderBy('games.id', 'asc');
        $gamesList = $gamesList->get();

        return $gamesList;
    }

    public function getWithoutAmazonUkLink()
    {
        $gamesList = Game::where('amazon_uk_link', null)->orderBy('id', 'asc')->get();
        return $gamesList;
    }

    public function getByNullField($field, $region)
    {
        $allowedFields = [
            'video_url', 'vendor_page_url', 'nintendo_page_url', 'twitter_id'
        ];

        if (!in_array($field, $allowedFields)) {
            throw new \Exception('Field '.$field.' not supported by getByMissingField');
        }

        $gamesList = DB::table('games')
            ->join('game_release_dates', 'games.id', '=', 'game_release_dates.game_id')
            ->select('games.*',
                'game_release_dates.release_date',
                'game_release_dates.is_released',
                'game_release_dates.upcoming_date',
                'game_release_dates.release_year')
            ->where('game_release_dates.region', $region)
            ->where($field, null)
            ->orderBy('game_release_dates.release_date', 'asc')
            //->orderBy('game_release_dates.upcoming_date', 'asc')
            ->orderBy('games.id', 'asc');
        $gamesList = $gamesList->get();

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
            case 'action-list-games-for-release':
                $gameList = $this->getActionListGamesForRelease($regionCode);
                break;
            case 'action-list-recent-no-nintendo-url':
                $gameList = $this->getActionListRecentNoNintendoUrl($regionCode);
                break;
            case 'action-list-upcoming-no-nintendo-url':
                $gameList = $this->getActionListUpcomingNoNintendoUrl($regionCode);
                break;
            case 'action-list-nintendo-url-no-packshots':
                $gameList = $this->getActionListNintendoUrlNoPackshots($regionCode);
                break;
            case 'no-dev-or-pub':
                $gameList = $this->getWithoutDevOrPub();
                break;
            case 'no-boxart':
                $gameList = $this->getWithoutBoxart($regionCode);
                break;
            case 'no-video-url':
                $gameList = $this->getByNullField('video_url', $regionCode);
                break;
            case 'no-vendor-page-url':
                $gameList = $this->getByNullField('vendor_page_url', $regionCode);
                break;
            case 'no-nintendo-page-url':
                $gameList = $this->getByNullField('nintendo_page_url', $regionCode);
                break;
            case 'no-twitter-id':
                $gameList = $this->getByNullField('twitter_id', $regionCode);
                break;
            case 'no-amazon-uk-link':
                $gameList = $this->getWithoutAmazonUkLink();
                break;
            default:
                return null;
                break;
        }

        if ($gameList == null) return null;

        $gameIdList = $gameList->pluck('id')->toArray();

        if (count($gameIdList) == 0) return null;

        $flipped = array_flip($gameIdList);

        if (count($flipped) == 0) return null;

        // currentId is the current game id we're editing.
        // gameIdList is an array of game IDs with the current filter,
        // with array indexes starting from zero.
        // The flipped array reverses the keys and values in the gameIdList array.
        // So for instance, if the first game id in this filter is 107,
        // the flipped array will start with key 107, value 0.
        // Whereas the gameIdList would have key 0, value 107.

        // This allows us to do the following:
        // 1. get the array index of the current game id.
        // e.g. game id 926 has an index of 289.
        // 2. add 1 to the index to get the next index.
        // e.g. the next index would be 290.
        // 3. get the next game id with this index from the gameIdList array.
        // e.g. index 290 is game id 663.

        $currentGameIndex = $flipped[$currentId];
        $nextGameIndex = $currentGameIndex + 1;

        // Make sure the next game index exists.
        if (!array_key_exists($nextGameIndex, $gameIdList)) {
            // This is the end of the array
            return null;
        } else {
            $nextId = $gameIdList[$nextGameIndex];
        }

        return $nextId;
    }
}