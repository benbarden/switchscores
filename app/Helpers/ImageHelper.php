<?php


namespace App\Helpers;

use App\Domain\Game\Repository as GameRepository;

use App\Services\Game\Images as GameImages;

class ImageHelper
{
    static function packshotHtmlBuilder($gameId, $page)
    {
        $repoGame = new GameRepository;
        $game = $repoGame->find($gameId);

        if (!$game) {
            return '<img src="/img/placeholder/no-image-found-square.png" style="border: 0; height: 75px;" alt="Nintendo Switch games">';
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

        if ($game->image_square) {
            $boxartPath = GameImages::PATH_IMAGE_SQUARE;
            $boxartUrl = $game->image_square;
        } elseif ($game->boxart_square_url) {
            $boxartPath = '/img/games/square/';
            $boxartUrl = $boxartPath . $game->boxart_square_url;
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

            $htmlOutput = '<img src="/img/placeholder/no-image-found-square.png" style="border: 0; height: '.$sizeInPixels.'px;" alt="'.$game->title.'">';

        } else {
            // do nothing
        }

        return $htmlOutput;
    }

    static function imageHeaderUrl($game)
    {
        if ($game->image_header) {
            $imageUrl = GameImages::PATH_IMAGE_HEADER.$game->image_header;
        } elseif ($game->boxart_header_image) {
            $imageUrl = '/img/games/header/'.$game->boxart_header_image;
        } else {
            $imageUrl = '';
        }

        return $imageUrl;
    }

    static function imageSquareUrl($game)
    {
        if ($game->image_square) {
            $imageUrl = GameImages::PATH_IMAGE_SQUARE.$game->image_square;
        } elseif ($game->boxart_square_url) {
            $imageUrl = '/img/games/square/'.$game->boxart_square_url;
        } else {
            $imageUrl = '';
        }

        return $imageUrl;
    }
}