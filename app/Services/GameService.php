<?php


namespace App\Services;

use App\Game;
use App\GameGenre;
use App\GamePrimaryType;
use App\GameSeries;
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
     * @param null $mediaFolder
     * @param null $videoUrl
     * @param null $boxartSquareUrl
     * @param null $eshopEuropeFsId
     * @param null $boxartHeaderImage
     * @return Game
     */
    public function create(
        $title, $linkTitle, $priceEshop, $players, $developer, $publisher,
        $amazonUkLink = null, $videoUrl = null,
        $boxartSquareUrl = null, $eshopEuropeFsId = null,
        $boxartHeaderImage = null
    )
    {
        return Game::create([
            'title' => $title,
            'link_title' => $linkTitle,
            'price_eshop' => $priceEshop,
            'players' => $players,
            'developer' => $developer,
            'publisher' => $publisher,
            'review_count' => 0,
            'amazon_uk_link' => $amazonUkLink,
            'video_url' => $videoUrl,
            'boxart_square_url' => $boxartSquareUrl,
            'eshop_europe_fs_id' => $eshopEuropeFsId,
            'boxart_header_image' => $boxartHeaderImage,
        ]);
    }

    public function edit(
        Game $game,
        $title, $linkTitle, $priceEshop, $players, $developer, $publisher,
        $amazonUkLink = null, $videoUrl = null,
        $boxartSquareUrl = null, $eshopEuropeFsId = null,
        $boxartHeaderImage = null
    )
    {
        $values = [
            'title' => $title,
            'link_title' => $linkTitle,
            'price_eshop' => $priceEshop,
            'players' => $players,
            'developer' => $developer,
            'publisher' => $publisher,
            'amazon_uk_link' => $amazonUkLink,
            'video_url' => $videoUrl,
            'boxart_square_url' => $boxartSquareUrl,
            'eshop_europe_fs_id' => $eshopEuropeFsId,
            'boxart_header_image' => $boxartHeaderImage,
        ];

        $game->fill($values);
        $game->save();
    }

    public function clearOldDeveloperField(Game $game)
    {
        $game->developer = null;
        $game->save();
    }

    public function clearOldPublisherField(Game $game)
    {
        $game->publisher = null;
        $game->save();
    }

    public function markAsReleased(Game $game)
    {
        $dateNow = new \DateTime('now');

        $game->eu_is_released = 1;
        $game->eu_released_on = $dateNow->format('Y-m-d H:i:s');
        $game->save();
    }

    public function deleteGame($gameId)
    {
        Game::where('id', $gameId)->delete();
    }

    // ********************************************************** //

    /**
     * @param $id
     * @return Game
     */
    public function find($id)
    {
        return Game::find($id);
    }

    /**
     * @param $title
     * @return Game
     */
    public function getByTitle($title)
    {
        $game = Game::where('title', $title)
            ->first();
        return $game;
    }

    /**
     * @param $region
     * @param $fsId
     * @return Game
     * @throws \Exception
     */
    public function getByFsId($region, $fsId)
    {
        switch ($region) {
            case 'eu':
                $field = 'eshop_europe_fs_id';
                break;
            default:
                throw new \Exception('Unsupported region: '.$region);
                break;
        }

        $game = Game::where($field, $fsId)->first();
        return $game;
    }

    // ********************************************************** //
    // Get number of ranked games
    // ********************************************************** //

    public function countRanked()
    {
        return Game::whereNotNull('game_rank')->count();
    }

    // ********************************************************** //
    // Rewritten lists for Staff > Games
    // ********************************************************** //

    public function getRecentlyAdded($limit = 100)
    {
        return Game::orderBy('id', 'desc')->limit($limit)->get();
    }

    public function getRecentlyReleased($limit = 100)
    {
        return Game::where('eu_is_released', 1)->orderBy('eu_release_date', 'desc')->orderBy('eu_released_on', 'desc')->limit($limit)->get();
    }

    // ********************************************************** //
    // Action lists.
    // These don't have a forced limit as we need to know the total
    // ********************************************************** //

    public function getWithNoNintendoCoUkLink($limit = null)
    {
        $gameList = Game::whereNull('eshop_europe_fs_id')->orderBy('id', 'desc');
        if ($limit) {
            $gameList = $gameList->limit($limit);
        }
        return $gameList->get();
    }

    // ********************************************************** //
    // Stuff to sort through
    // ********************************************************** //

    public function getAll()
    {
        $games = DB::table('games')
            ->select('games.*')
            ->orderBy('games.title', 'asc');
        $games = $games->get();

        return $games;
    }

    public function getCount()
    {
        return Game::orderBy('title', 'asc')->count();
    }

    public function getAllWithEuropeEshopId()
    {
        $games = Game::whereNotNull('games.eshop_europe_fs_id')
            ->orderBy('games.title', 'asc')
            ->get();

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

    // Used for admin only
    public function getByDeveloper($developer)
    {
        return Game::where('developer', $developer)->orderBy('title', 'asc')->get();
    }

    // Used for admin only
    public function getByPublisher($publisher)
    {
        return Game::where('publisher', $publisher)->orderBy('title', 'asc')->get();
    }

    public function getByPrimaryType(GamePrimaryType $primaryType)
    {
        return Game::where('primary_type_id', $primaryType->id)->orderBy('title', 'asc')->get();
    }

    public function getBySeries(GameSeries $gameSeries)
    {
        return Game::where('series_id', $gameSeries->id)->orderBy('title', 'asc')->get();
    }

    public function getByIdList($idList)
    {
        $gamesList = Game::whereIn('id', $idList)->orderBy('title', 'asc')->get();
        return $gamesList;
    }

    /**
     * @param int $limit
     * @return mixed
     */
    public function getActionListGamesForRelease($limit = null)
    {
        $games = DB::table('games')
            ->select('games.*')
            ->where('games.eu_is_released', 0)
            ->whereRaw('DATE(games.eu_release_date) <= CURDATE()');

        $games = $games->orderBy('games.eu_release_date', 'asc')
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
    public function getEshopEuropeNoPackshots($limit = null)
    {
        $games = DB::table('games')
            ->join('eshop_europe_games', 'games.eshop_europe_fs_id', '=', 'eshop_europe_games.fs_id')
            ->select('games.*')
            ->whereNotNull('games.eshop_europe_fs_id')
            ->where(function($q) {
                $q->whereNull('boxart_square_url')->orWhereNull('boxart_header_image');
            })
            ->orderBy('games.eu_release_date', 'asc')
            ->orderBy('games.title', 'asc');

        if ($limit != null) {
            $games = $games->limit($limit);
        }
        $games = $games->get();

        return $games;
    }

    public function getWithoutBoxart()
    {
        $gamesList = DB::table('games')
            ->select('games.*')
            ->where('boxart_square_url', null)
            ->orderBy('games.eu_release_date', 'asc')
            ->orderBy('games.id', 'asc');
        $gamesList = $gamesList->get();

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
            'video_url', 'eshop_europe_fs_id'
        ];

        if (!in_array($field, $allowedFields)) {
            throw new \Exception('Field '.$field.' not supported by getByMissingField');
        }

        $gamesList = DB::table('games')
            ->select('games.*')
            ->where($field, null)
            ->orderBy('games.eu_release_date', 'asc')
            ->orderBy('games.id', 'asc');
        $gamesList = $gamesList->get();

        return $gamesList;
    }

    public function getNextId($filter, $currentId)
    {
        $gameList = null;

        switch ($filter) {
            case 'no-boxart':
                $gameList = $this->getWithoutBoxart();
                break;
            case 'no-eshop-europe-link':
                $gameList = $this->getByNullField('eshop_europe_fs_id');
                break;
            case 'no-video-url':
                $gameList = $this->getByNullField('video_url');
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

    // *** STATS *** //

    public function getOldDevelopersMultiple()
    {
        $games = DB::select("
            select id, title, developer
            from games
            where developer like '%,%';
        ");

        return $games;
    }

    public function getOldPublishersMultiple()
    {
        $games = DB::select("
            select id, title, publisher
            from games
            where publisher like '%,%';
        ");

        return $games;
    }

    public function getOldDevelopersByCount()
    {
        $games = DB::select("
            select g.developer, count(*) AS count
            from games g
            left join game_developers gd on g.id = gd.game_id
            left join partners d on gd.developer_id = d.id
            where d.id is null or g.developer is not null
            group by g.developer
            order by count(*) desc, g.developer asc
        ");

        return $games;
    }

    public function getOldPublishersByCount()
    {
        $games = DB::select("
            select g.publisher, count(*) AS count
            from games g
            left join game_publishers gp on g.id = gp.game_id
            left join partners p on p.id = gp.publisher_id
            where p.id is null or g.publisher is not null
            group by g.publisher
            order by count(*) desc, g.publisher asc
        ");

        return $games;
    }

    // ** ACTION LISTS (New) ** //

    public function countWithoutPrices()
    {
        return Game::whereNull('price_eshop')
            ->orderBy('id', 'asc')
            ->count();
    }

    public function getWithoutPrices()
    {
        return Game::whereNull('price_eshop')
            ->orderBy('id', 'asc')
            ->get();
    }

    // Series
    public function getSeriesTitleMatch($series)
    {
        return Game::where('title', 'LIKE', $series.'%')
            ->whereNull('series_id')
            ->orderBy('id', 'asc')
            ->get();
    }

    // Tag
    // NB. This doesn't de-dupe games that already have the tag - that is done separately
    public function getTagTitleMatch($tagName)
    {
        return Game::where('title', 'LIKE', '%'.$tagName.'%')
            ->orderBy('id', 'asc')
            ->get();
    }

}