<?php


namespace App\Helpers;

use App\Domain\Game\Repository as GameRepository;
use App\Domain\Game\ImageResolver;

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

        $boxartUrl = self::imageSquareUrl($game);

        $htmlOutput = '';

        if ($boxartUrl) {

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
        return app(ImageResolver::class)->url($game, ImageResolver::TYPE_HEADER);
    }

    static function imageSquareUrl($game)
    {
        return app(ImageResolver::class)->url($game, ImageResolver::TYPE_SQUARE);
    }
}
