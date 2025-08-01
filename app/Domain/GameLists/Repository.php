<?php


namespace App\Domain\GameLists;

use App\Models\DataSourceParsed;
use App\Models\Game;
use App\Models\GameSeries;
use App\Models\DataSource;

use Illuminate\Support\Facades\DB;

class Repository
{
    /**
     * @deprecated
     * @return \Illuminate\Support\Collection
     */
    public function getAll()
    {
        return DB::table('games')->select('games.*')->orderBy('games.title', 'asc')->get();
    }

    public function allGames()
    {
        return Game::orderBy('title', 'asc')->get();
    }

    public function getApiIdList()
    {
        $gameList = DB::table('games')
            ->select('games.id', 'games.title', 'games.link_title', 'games.eshop_europe_fs_id', 'games.updated_at')
            ->orderBy('games.id', 'asc')
            ->get();
        return $gameList;
    }

    public function recentlyReleased($consoleId, $includeLowQuality = false, $limit = 100)
    {
        $games = Game::where('console_id', $consoleId)->where('eu_is_released', 1);

        if (!$includeLowQuality) {
            $games = $games->where('is_low_quality', 0);
        }

        $games = $games->orderBy('eu_release_date', 'desc')
            ->orderBy('eu_released_on', 'desc')
            ->orderBy('updated_at', 'desc')
            ->orderBy('title', 'asc')
            ->limit($limit)
            ->get();

        return $games;
    }

    public function recentlyReleasedAll($consoleId, $limit = 100)
    {
        return $this->recentlyReleased($consoleId, true, $limit);
    }

    public function recentlyReleasedExceptLowQuality($consoleId, $limit = 100)
    {
        return $this->recentlyReleased($consoleId, false, $limit);
    }

    public function recentlyAdded($limit = 100)
    {
        return Game::orderBy('id', 'desc')->limit($limit)->get();
    }

    /**
     * Upcoming games list, excluding low quality games. Use for public lists.
     * @param $limit
     * @return mixed
     */
    public function upcoming($consoleId, $limit = null)
    {
        $games = Game::where('console_id', $consoleId)
            ->where('eu_is_released', 0)
            ->where('is_low_quality', 0)
            ->whereNotNull('games.eu_release_date')
            ->orderBy('eu_release_date', 'asc')
            ->orderBy('eshop_europe_order', 'asc')
            ->orderBy('title', 'asc');

        if ($limit != null) {
            $games = $games->limit($limit);
        }
        $games = $games->get();

        return $games;
    }

    /**
     * All upcoming games, regardless of quality. Use for staff pages.
     * @param $limit
     * @return mixed
     */
    public function upcomingAll($limit = null)
    {
        $games = Game::where('eu_is_released', 0)
            ->whereNotNull('games.eu_release_date')
            ->orderBy('eu_release_date', 'asc')
            ->orderBy('eshop_europe_order', 'asc')
            ->orderBy('title', 'asc');

        if ($limit != null) {
            $games = $games->limit($limit);
        }
        $games = $games->get();

        return $games;
    }

    public function upcomingNextDays($consoleId, $days, $limit = null)
    {
        $games = Game::where('console_id', $consoleId)
            ->where('eu_is_released', 0)
            ->whereNotNull('games.eu_release_date')
            ->whereRaw('eu_release_date < DATE_ADD(NOW(), INTERVAL ? DAY)', $days)
            ->orderBy('eu_release_date', 'asc')
            ->orderBy('eshop_europe_order', 'asc')
            ->orderBy('title', 'asc');

        if ($limit != null) {
            $games = $games->limit($limit);
        }
        $games = $games->get();

        return $games;
    }

    public function upcomingBetweenDays($consoleId, $startDays, $endDays, $limit = null)
    {
        $games = Game::where('console_id', $consoleId)
            ->where('eu_is_released', 0)
            ->whereNotNull('games.eu_release_date')
            ->whereRaw('eu_release_date >= DATE_ADD(NOW(), INTERVAL ? DAY)', $startDays)
            ->whereRaw('eu_release_date < DATE_ADD(NOW(), INTERVAL ? DAY)', $endDays)
            ->orderBy('eu_release_date', 'asc')
            ->orderBy('eshop_europe_order', 'asc')
            ->orderBy('title', 'asc');

        if ($limit != null) {
            $games = $games->limit($limit);
        }
        $games = $games->get();

        return $games;
    }

    public function upcomingBeyondDays($consoleId, $days, $limit = null)
    {
        $games = Game::where('console_id', $consoleId)
            ->where('eu_is_released', 0)
            ->whereNotNull('games.eu_release_date')
            ->whereRaw('eu_release_date >= DATE_ADD(NOW(), INTERVAL ? DAY)', $days)
            ->orderBy('eu_release_date', 'asc')
            ->orderBy('eshop_europe_order', 'asc')
            ->orderBy('title', 'asc');

        if ($limit != null) {
            $games = $games->limit($limit);
        }
        $games = $games->get();

        return $games;
    }

    public function noCategoryExcludingLowQuality()
    {
        return Game::whereNull('category_id')->where('is_low_quality', 0)->get();
    }

    public function noCategoryAll()
    {
        return Game::whereNull('category_id')->get();
    }

    public function noCategoryWithCollection()
    {
        return Game::whereNotNull('collection_id')->whereNull('category_id')->get();
    }

    public function noCategoryWithReviews()
    {
        return Game::where('review_count', '>', 0)->whereNull('category_id')->get();
    }

    public function byIdList($idList)
    {
        return Game::whereIn('id', $idList)->get();
    }

    public function byCollection($consoleId, $collectionId, $limit = null)
    {
        $games = Game::where('console_id', $consoleId)
            ->where('collection_id', $collectionId)
            ->where('eu_is_released', 1)
            ->orderBy('title', 'asc');

        if ($limit) {
            $games = $games->limit($limit);
        }

        return $games->get();
    }

    public function byCollectionAndCategory($collectionId, $categoryId)
    {
        $games = Game::where('collection_id', $collectionId)
            ->where('category_id', $categoryId)
            ->where('eu_is_released', 1)
            ->orderBy('title', 'asc');

        return $games->get();
    }

    public function byCollectionAndSeries($collectionId, $seriesId)
    {
        $games = Game::where('collection_id', $collectionId)
            ->where('series_id', $seriesId)
            ->where('eu_is_released', 1)
            ->orderBy('title', 'asc');

        return $games->get();
    }

    public function bySeries(GameSeries $gameSeries)
    {
        return Game::where('series_id', $gameSeries->id)->orderBy('title', 'asc')->get();
    }

    public function bySeriesWithImages(GameSeries $gameSeries, $limit = null)
    {
        $games = Game::where('series_id', $gameSeries->id)
            ->whereNotNull('image_header')
            ->whereNotNull('image_square')
            ->orderBy('rating_avg', 'desc');

        if ($limit) {
            $games = $games->limit($limit);
        }

        return $games->get();
    }

    public function recentWithGoodRanksByConsole($consoleId, $minimumRating = 7, $dateInterval = 30, $limit = 15)
    {
        $games = Game::where('games.eu_is_released', 1)
            ->where('games.console_id', $consoleId)
            ->whereRaw('games.eu_release_date between date_sub(NOW(), INTERVAL ? DAY) and now()', $dateInterval)
            ->whereNotNull('games.game_rank')
            ->where('games.rating_avg', '>', $minimumRating)
            ->orderBy('games.rating_avg', 'desc')
            ->orderBy('games.eu_release_date', 'desc')
            ->orderBy('games.title', 'asc');

        if ($limit != null) {
            $games = $games->limit($limit);
        }
        $games = $games->get();

        return $games;
    }

    /**
     * @deprecated
     * @param $minimumRating
     * @param $dateInterval
     * @param $limit
     * @return mixed
     */
    public function recentWithGoodRanks($minimumRating = 7, $dateInterval = 30, $limit = 15)
    {
        $games = Game::where('games.eu_is_released', 1)
            ->whereRaw('games.eu_release_date between date_sub(NOW(), INTERVAL ? DAY) and now()', $dateInterval)
            ->whereNotNull('games.game_rank')
            ->where('games.rating_avg', '>', $minimumRating)
            ->orderBy('games.rating_avg', 'desc')
            ->orderBy('games.eu_release_date', 'desc')
            ->orderBy('games.title', 'asc');

        if ($limit != null) {
            $games = $games->limit($limit);
        }
        $games = $games->get();

        return $games;
    }

    public function recentlyReleasedByCategory($categoryId, $limit = null)
    {
        $games = Game::where('category_id', $categoryId)
            ->where('eu_is_released', 1)
            ->where('format_digital', '<>', Game::FORMAT_DELISTED)
            ->orderBy('eu_release_date', 'desc');

        if ($limit) {
            $games = $games->limit($limit);
        }

        return $games->get();

    }
    public function upcomingEshopCrosscheck($consoleId, $limit = null)
    {
        $games = Game::where('eu_is_released', 0)
            ->where('console_id', $consoleId)
            ->whereNotNull('games.eu_release_date')
            ->orderBy('eu_release_date', 'asc')
            ->orderBy('eshop_europe_order', 'asc')
            ->orderBy('id', 'asc');

        if ($limit != null) {
            $games = $games->limit($limit);
        }
        $games = $games->get();

        return $games;
    }

    public function upcomingEshopCrosscheckNoDate($limit = null)
    {
        $games = Game::where('eu_is_released', 0)
            ->whereNull('games.eu_release_date')
            ->whereNotNull('games.eshop_europe_fs_id')
            ->orderBy('eshop_europe_order', 'asc')
            ->orderBy('games.id', 'desc');

        if ($limit != null) {
            $games = $games->limit($limit);
        }
        $games = $games->get();

        return $games;
    }

    public function gamesForRelease()
    {
        $games = Game::where('eu_is_released', 0)
            ->whereRaw('DATE(games.eu_release_date) <= CURDATE()')
            ->orderBy('eu_release_date', 'asc')
            ->orderBy('title', 'asc')
            ->get();

        return $games;
    }

    public function formatOption($format, $value = null)
    {
        $allowedFormats = ['Physical', 'Digital', 'DLC', 'Demo'];
        if (!$allowedFormats) throw new \Exception('Unknown format: '.$format);

        $dbField = '';
        switch ($format) {
            case 'Physical':
                $dbField = 'format_physical';
                break;
            case 'Digital':
                $dbField = 'format_digital';
                break;
            case 'DLC':
                $dbField = 'format_dlc';
                break;
            case 'Demo':
                $dbField = 'format_demo';
                break;
        }

        if (!$dbField) throw new \Exception('Cannot determine dbField!');

        if ($value == null) {
            $games = Game::whereNull($dbField)->get();
        } else {
            $games = Game::where($dbField, $value)->get();
        }

        return $games;
    }

    public function noVideoType($limit = 200)
    {
        return Game::whereNull('video_type')->orderBy('id', 'asc')->limit($limit)->get();
    }

    public function noPrice()
    {
        return Game::whereNull('price_eshop')->orderBy('id', 'asc')->get();
    }

    public function noTag()
    {
        return Game::whereDoesntHave('gameTags')->get();
    }

    public function noEuReleaseDate()
    {
        return Game::whereNull('eu_release_date')->orderBy('id', 'desc')->get();
    }

    public function noAmazonUkLink($limit = 200)
    {
        return Game::where('format_physical', Game::FORMAT_AVAILABLE)
            ->whereNull('amazon_uk_link')
            ->orderBy('rating_avg', 'desc')
            ->orderBy('review_count', 'desc')
            ->limit($limit)
            ->get();
    }

    public function noAmazonUsLink($limit = 200)
    {
        return Game::where('format_physical', Game::FORMAT_AVAILABLE)
            ->whereNull('amazon_us_link')
            ->orderBy('rating_avg', 'desc')
            ->orderBy('review_count', 'desc')
            ->limit($limit)
            ->get();
    }

    public function relatedByCategory($categoryId, $excludeGameId, $limit = 3)
    {
        return Game::where('category_id', $categoryId)
            ->where('id', '<>', $excludeGameId)
            ->where('eu_is_released', 1)
            ->whereNotNull('game_rank')
            ->where('rating_avg', '>=', '7.0')
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }

    public function relatedBySeries($seriesId, $excludeGameId, $limit = 3)
    {
        return Game::where('series_id', $seriesId)
            ->where('id', '<>', $excludeGameId)
            ->where('eu_is_released', 1)
            ->whereNotNull('game_rank')
            ->where('rating_avg', '>=', '7.0')
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }

    public function relatedByCollection($collectionId, $excludeGameId, $limit = 3)
    {
        return Game::where('collection_id', $collectionId)
            ->where('id', '<>', $excludeGameId)
            ->where('eu_is_released', 1)
            ->whereNotNull('game_rank')
            ->where('rating_avg', '>=', '7.0')
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }

    public function anyWithNintendoCoUkId()
    {
        return Game::whereNotNull('eshop_europe_fs_id')
            ->orderBy('id')
            ->get();
    }

    public function anyWithStoreOverride()
    {
        return Game::whereNotNull('nintendo_store_url_override')
            ->orderBy('id')
            ->get();
    }

    public function noNintendoCoUkIdWithStoreOverride($limit = 5)
    {
        return Game::whereNull('eshop_europe_fs_id')
            ->whereNotNull('nintendo_store_url_override')
            ->whereNull('image_square')
            //->whereNull('image_header')
            ->orderBy('id')
            ->limit($limit)
            ->get();
    }

    public function noNintendoCoUkLink($limit = null)
    {
        $gameList = Game::whereNull('eshop_europe_fs_id')
            ->whereNull('nintendo_store_url_override')
            ->whereNotNull('eu_release_date')
            ->where('format_digital', '<>', Game::FORMAT_DELISTED)
            ->orderBy('id', 'desc');
        if ($limit) {
            $gameList = $gameList->limit($limit);
        }
        return $gameList->get();
    }

    public function brokenNintendoCoUkLink($limit = null)
    {
        $gameList = DB::table('games')
            ->select('games.*')
            ->leftJoin('data_source_parsed', 'games.eshop_europe_fs_id', '=', 'data_source_parsed.link_id')
            ->whereNotNull('games.eshop_europe_fs_id')
            ->whereNull('data_source_parsed.link_id')
            ->whereNull('games.nintendo_store_url_override');
        if ($limit) {
            $gameList = $gameList->limit($limit);
        }
        return $gameList->get();
    }

    public function anyWithNintendoCoUkIdOrStoreOverride($limit = 5)
    {
        return Game::whereNull('eshop_europe_fs_id')
            ->orWhereNotNull('nintendo_store_url_override')
            ->orderBy('id')
            ->limit($limit)
            ->get();
    }

    public function byYearWeek($year, $week, $isLowQuality = null)
    {
        // fix: week needs to be -1 as MySQL is zero-indexed?
        $week--;

        if ($week < 10) {
            $week = str_pad($week, 2, '0', STR_PAD_LEFT);
        }
        $gameList = Game::where('eu_is_released', 1);
        $gameList = $gameList->where(DB::raw('YEARWEEK(eu_release_date)'), $year.$week);

        if ($isLowQuality == true) {
            $gameList = $gameList->where('is_low_quality', 1);
        } elseif ($isLowQuality == false) {
            $gameList = $gameList->where('is_low_quality', 0);
        }

        $gameList = $gameList
            ->orderBy('games.eu_release_date')
            ->orderBy('games.title')
            ->limit(100)
            ->get();

        return $gameList;
    }

    public function upcomingSwitchWeekly($consoleId, $daysLimit)
    {
        return Game::where('games.console_id', $consoleId)
            ->where('games.eu_is_released', 0)
            ->whereNotNull('games.eu_release_date')
            ->whereRaw('eu_release_date < DATE_ADD(NOW(), INTERVAL ? DAY)', $daysLimit)
            ->orderBy('games.eu_release_date', 'asc')
            ->orderBy('games.title', 'asc')
            ->get();
    }
}