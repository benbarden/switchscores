<?php


namespace App\Domain\GameLists;

use App\Models\Game;
use App\Models\GameSeries;

class Repository
{
    public function recentlyReleased($limit = 100)
    {
        $games = Game::where('eu_is_released', 1)
            ->where('is_low_quality', 0)
            ->orderBy('eu_release_date', 'desc')
            ->orderBy('eu_released_on', 'desc')
            ->orderBy('updated_at', 'desc')
            ->orderBy('title', 'asc')
            ->limit($limit)
            ->get();

        return $games;
    }

    public function recentlyAdded($limit = 100)
    {
        return Game::orderBy('id', 'desc')->limit($limit)->get();
    }

    public function upcoming($limit = null)
    {
        $games = Game::where('eu_is_released', 0)
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

    public function upcomingNextDays($days, $limit = null)
    {
        $games = Game::where('eu_is_released', 0)
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

    public function upcomingBetweenDays($startDays, $endDays, $limit = null)
    {
        $games = Game::where('eu_is_released', 0)
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

    public function upcomingBeyondDays($days, $limit = null)
    {
        $games = Game::where('eu_is_released', 0)
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

    public function byCategory($categoryId, $limit = null)
    {
        $games = Game::where('category_id', $categoryId)
            ->where('eu_is_released', 1)
            ->orderBy('title', 'asc');

        if ($limit) {
            $games = $games->limit($limit);
        }

        return $games->get();
    }

    public function noCategory()
    {
        return Game::whereNull('category_id')->get();
    }

    public function byCollection($collectionId, $limit = null)
    {
        $games = Game::where('collection_id', $collectionId)
            ->where('eu_is_released', 1)
            ->orderBy('title', 'asc');

        if ($limit) {
            $games = $games->limit($limit);
        }

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

    public function rankedByCategory($categoryId, $limit = null)
    {
        $games = Game::where('category_id', $categoryId)
            ->where('eu_is_released', 1)
            ->whereNotNull('game_rank')
            ->where('format_digital', '<>', Game::FORMAT_DELISTED)
            ->orderBy('game_rank', 'asc')
            ->orderBy('title', 'asc');

        if ($limit) {
            $games = $games->limit($limit);
        }

        return $games->get();

    }

    public function unrankedByCategory($categoryId, $limit = null)
    {
        $games = Game::where('category_id', $categoryId)
            ->where('eu_is_released', 1)
            ->whereNull('game_rank')
            ->where('format_digital', '<>', Game::FORMAT_DELISTED)
            ->orderBy('review_count', 'desc')
            ->orderBy('title', 'asc');

        if ($limit) {
            $games = $games->limit($limit);
        }

        return $games->get();

    }

    public function delistedByCategory($categoryId, $limit = null)
    {
        $games = Game::where('category_id', $categoryId)
            ->where('eu_is_released', 1)
            ->whereNull('game_rank')
            ->where('format_digital', '=', Game::FORMAT_DELISTED)
            ->orderBy('title', 'asc')
            ->orderBy('eu_release_date', 'asc');

        if ($limit) {
            $games = $games->limit($limit);
        }

        return $games->get();

    }

    public function upcomingEshopCrosscheck($limit = null)
    {
        $games = Game::where('eu_is_released', 0)
            ->whereNotNull('games.eu_release_date')
            ->orderBy('eu_release_date', 'asc')
            ->orderBy('eshop_europe_order', 'asc');

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

    public function noTag()
    {
        return Game::whereDoesntHave('gameTags')->get();
    }

    public function relatedByCategory($categoryId, $excludeGameId, $limit = 3)
    {
        return Game::where('category_id', $categoryId)
            ->where('id', '<>', $excludeGameId)
            ->where('eu_is_released', 1)
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
            ->where('rating_avg', '>=', '7.0')
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }

    public function noNintendoCoUkIdWithStoreOverride($limit = 5)
    {
        return Game::whereNull('eshop_europe_fs_id')
            ->whereNotNull('nintendo_store_url_override')
            ->whereNull('image_square')
            ->whereNull('image_header')
            ->orderBy('id')
            ->limit($limit)
            ->get();
    }
}