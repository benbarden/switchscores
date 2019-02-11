<?php


namespace App\Helpers;

use App\Services\ServiceContainer;

use App\Game;


class ImageHelper
{
    static function packshotHtmlBuilder($gameId, $page)
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $gameService = $serviceContainer->getGameService();
        $game = $gameService->find($gameId);

        if (!$game) {
            return '<img src="/img/logo-grey.png" style="border: 0; height: 75px;" alt="Nintendo Switch games">';
        }

        return self::packshotHtml($game, $page);
    }

    static function packshotHtml($game, $page, $isReleased = 1)
    {
        switch ($page) {
            case 'game-square-header':
                $sizeInPixels = 250;
                $showEmptyCell = true;
                break;
            case 'game-show':
                $sizeInPixels = 125;
                $showEmptyCell = true;
                break;
            case 'release-calendar':
                $sizeInPixels = 75;
                $showEmptyCell = false;
                break;
            case 'landing-mini':
                $sizeInPixels = 75;
                $showEmptyCell = true;
                break;
            case 'reviews-tiny':
                $sizeInPixels = 60;
                $showEmptyCell = true;
                break;
            default:
                $sizeInPixels = 125;
                $showEmptyCell = false;
                break;
        }

        if ($game->boxart_square_url) {
            $boxartPath = '/img/games/square/';
            $boxartUrl = $boxartPath.$game->boxart_square_url;
        } else {
            $boxartPath = null;
            $boxartUrl = null;
        }

        $htmlOutput = '';

        if (!is_null($boxartPath) && !is_null($boxartUrl)) {

            if ($isReleased == 1) {
                $opacityStyle = '';
            } else {
                $opacityStyle = ' opacity: 0.4;';
            }

            $htmlOutput = '<img src="'.$boxartUrl.'" style="border: 0; height: '.$sizeInPixels.'px;'.$opacityStyle.'" alt="'.$game->title.'">';

        } elseif ($showEmptyCell) {

            $htmlOutput = '<img src="/img/logo-grey.png" style="border: 0; height: '.$sizeInPixels.'px;" alt="'.$game->title.'">';
            /*
            $htmlOutput = '<div style="background: #ccc; '.
                'height: '.$sizeInPixels.'px; width: '.$sizeInPixels.'px; '.
                'text-align: center; margin: 0 auto;"></div>';
            */

        } else {
            // do nothing
        }

        return $htmlOutput;
    }

    static function packshotHeaderHtml($game)
    {
        if ($game->boxart_header_image) {
            $boxartPath = '/img/games/header/';
            $boxartUrl = $boxartPath.$game->boxart_header_image;
        } else {
            $boxartPath = null;
            $boxartUrl = null;
        }

        $htmlOutput = '';

        if (!is_null($boxartPath) && !is_null($boxartUrl)) {

            $htmlOutput = '<img src="'.$boxartUrl.'" class="img-responsive" style="border: 0;" alt="'.$game->title.'">';

        } else {
            // do nothing
        }

        return $htmlOutput;
    }
}