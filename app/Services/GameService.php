<?php


namespace App\Services;

use App\Models\Category;
use App\Models\Game;
use App\Models\GameSeries;
use App\Models\GameTag;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;


class GameService
{
    const ORDER_TITLE = 0;
    const ORDER_NEWEST = 1;
    const ORDER_OLDEST = 2;

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
     * @return \App\Models\Game
     */
    public function getByTitle($title)
    {
        return Game::where('title', $title)->first();
    }

    /**
     * @param $linkId
     * @return Game
     */
    public function getByEshopEuropeId($linkId)
    {
        return Game::where('eshop_europe_fs_id', $linkId)->first();
    }

    public function searchByTitle($keywords)
    {
        return Game::where('title', 'like', '%'.$keywords.'%')
            ->orderBy('eu_release_date', 'DESC')
            ->get();
    }

    public function getApiIdList()
    {
        $gameList = DB::table('games')
            ->select('games.id', 'games.title', 'games.link_title', 'games.eshop_europe_fs_id', 'games.updated_at')
            ->orderBy('games.id', 'asc')
            ->get();
        return $gameList;
    }

    /**
     * @param $idList
     * @param string[] $orderBy
     * @return \Illuminate\Support\Collection
     */
    public function getByIdList($idList, $orderBy = "")
    {
        if ($orderBy) {
            list($orderField, $orderDir) = $orderBy;
        } else {
            list($orderField, $orderDir) = ['id', 'desc'];
        }

        $idList = str_replace('&quot;', '', $idList);
        $idList = explode(",", $idList);

        $games = DB::table('games')
            ->select('games.*')
            ->whereIn('games.id', $idList)
            ->orderBy($orderField, $orderDir)
        ;

        $games = $games->get();

        return $games;
    }

    // ********************************************************** //
    // Rewritten lists for Staff > Games
    // ********************************************************** //

    public function getRecentlyReleased($limit = 100)
    {
        return Game::where('eu_is_released', 1)->orderBy('eu_release_date', 'desc')->orderBy('eu_released_on', 'desc')->limit($limit)->get();
    }

    public function getWithNoAmazonUkLink($limit = 200)
    {
        return Game::where('format_physical', Game::FORMAT_AVAILABLE)
            ->whereNull('amazon_uk_link')
            ->orderBy('id', 'asc')
            ->limit($limit)
            ->get();
    }

    public function countWithNoAmazonUkLink()
    {
        return Game::where('format_physical', Game::FORMAT_AVAILABLE)
            ->whereNull('amazon_uk_link')
            ->orderBy('id', 'asc')
            ->count();
    }

    // ********************************************************** //
    // Action lists.
    // These don't have a forced limit as we need to know the total
    // ********************************************************** //

    public function getWithNoNintendoCoUkLink($limit = null)
    {
        $gameList = Game::whereNull('eshop_europe_fs_id')->whereNotNull('eu_release_date')->orderBy('id', 'desc');
        if ($limit) {
            $gameList = $gameList->limit($limit);
        }
        return $gameList->get();
    }

    public function getWithBrokenNintendoCoUkLink($limit = null)
    {
        $gameList = DB::table('games')
            ->select('games.*')
            ->leftJoin('data_source_parsed', 'games.eshop_europe_fs_id', '=', 'data_source_parsed.link_id')
            ->whereNotNull('games.eshop_europe_fs_id')
            ->whereNull('data_source_parsed.link_id');
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

    public function getGamesForSitemap()
    {
        $games = DB::table('games')
            ->select('games.*')
            ->where('format_digital', '<>', Game::FORMAT_DELISTED)
            ->orderBy('games.title', 'asc');
        $games = $games->get();

        return $games;
    }

    public function getAllAsObjects()
    {
        return Game::orderBy('id', 'asc')->get();
    }

    public function getByCategory(Category $category)
    {
        return Game::where('category_id', $category->id)->orderBy('title', 'asc')->get();
    }

    public function getBySeries(GameSeries $gameSeries)
    {
        return Game::where('series_id', $gameSeries->id)->orderBy('title', 'asc')->get();
    }

    /**
     * @param $tagId
     * @return mixed
     */
    public function getByTag($tagId)
    {
        $games = DB::table('games')
            ->join('game_tags', 'games.id', '=', 'game_tags.game_id')
            ->join('tags', 'game_tags.tag_id', '=', 'tags.id')
            ->select('games.*',
                'game_tags.tag_id',
                'games.id AS game_id',
                'game_tags.id AS game_tag_id',
                'tags.tag_name')
            ->where('game_tags.tag_id', $tagId)
            ->orderBy('games.id', 'desc');

        $games = $games->get();
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

    // Category
    public function getCategoryTitleMatch($category)
    {
        return Game::where('title', 'LIKE', $category.'%')
            ->whereNull('category_id')
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
    public function getTagTitleMatch(Tag $tag)
    {
        $gamesWithTag = GameTag::where('tag_id', $tag->id)->pluck('game_id');

        return Game::where('title', 'LIKE', '%'.$tag->tag_name.'%')
            ->whereNotIn('id', $gamesWithTag)
            ->orderBy('id', 'asc')
            ->get();
    }

    public function getFormatOptionsPhysical()
    {
        $options = [];
        $options[] = Game::FORMAT_AVAILABLE;
        $options[] = Game::FORMAT_INCLUDED_IN_BUNDLE;
        $options[] = Game::FORMAT_LIMITED_EDITION;
        $options[] = Game::FORMAT_NOT_AVAILABLE;

        return $options;
    }

    public function getFormatOptionsDigital()
    {
        $options = [];
        $options[] = Game::FORMAT_AVAILABLE;
        $options[] = Game::FORMAT_DELISTED;
        $options[] = Game::FORMAT_NOT_AVAILABLE;

        return $options;
    }

    public function getFormatOptionsDLC()
    {
        $options = [];
        $options[] = Game::FORMAT_AVAILABLE;
        $options[] = Game::FORMAT_NOT_AVAILABLE;

        return $options;
    }

    public function getFormatOptionsDemo()
    {
        $options = [];
        $options[] = Game::FORMAT_AVAILABLE;
        $options[] = Game::FORMAT_NOT_AVAILABLE;

        return $options;
    }
}