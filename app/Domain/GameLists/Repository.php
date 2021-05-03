<?php


namespace App\Domain\GameLists;

use App\Game;

class Repository
{
    public function recentlyReleased($limit = 100)
    {
        $games = Game::where('eu_is_released', 1)
            ->orderBy('eu_release_date', 'desc')
            ->orderBy('eu_released_on', 'desc')
            ->orderBy('updated_at', 'desc')
            ->orderBy('title', 'asc')
            ->limit($limit)
            ->get();

        return $games;
    }

    public function upcoming($limit = null)
    {
        $games = Game::where('eu_is_released', 0)
            ->whereNotNull('games.eu_release_date')
            ->orderBy('eu_release_date', 'asc')
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
}