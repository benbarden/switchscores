<?php


namespace App\Domain\GameLists;


use App\Game;
use App\Services\GameService;

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
}