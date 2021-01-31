<?php


namespace App\Helpers;

class LinkHelper
{
    static function gameShow($game)
    {
        return route('game.show', ['id' => $game->id, 'linkTitle' => $game->link_title]);
    }

    static function eshopUrl($region, $url)
    {
        if (substr($url, 0, 1) != '/') {
            $url = '/'.$url;
        }

        switch ($region) {
            case 'eu':
                $eshopUrl = 'https://www.nintendo.co.uk'.$url;
                break;
            default:
                $eshopUrl = $url;
                break;
        }
        return $eshopUrl;
    }
}