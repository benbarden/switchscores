<?php


namespace App\Helpers;

use App\Game;


class ImageHelper
{
    static function packshotHtml($game, $page)
    {
        switch ($page) {
            case 'game-show':
                $sizeInPixels = 125;
                $showEmptyCell = true;
                break;
            case 'release-calendar':
                $sizeInPixels = 75;
                $showEmptyCell = false;
                break;
            default:
                $sizeInPixels = 125;
                $showEmptyCell = false;
                break;
        }

        if ($game->boxart_url) {
            $boxartPath = '/img/games/boxart/';
            $boxartUrl = $boxartPath.$game->boxart_url;
        } elseif ($game->boxart_square_url) {
            $boxartPath = '/img/games/square/';
            $boxartUrl = $boxartPath.$game->boxart_square_url;
        } else {
            $boxartPath = null;
            $boxartUrl = null;
        }

        $htmlOutput = '';

        if (!is_null($boxartPath) && !is_null($boxartUrl)) {

            $htmlOutput = '<img src="'.$boxartUrl.'" style="height: '.$sizeInPixels.'px;" alt="'.$game->title.'">';

        } elseif ($showEmptyCell) {

            $htmlOutput = '<div style="background: #ccc; '.
                'height: '.$sizeInPixels.'px; width: '.$sizeInPixels.'px; '.
                'text-align: center; margin: 0 auto;"></div>';

        } else {
            // do nothing
        }

        return $htmlOutput;
    }
}