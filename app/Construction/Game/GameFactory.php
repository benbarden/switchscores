<?php

namespace App\Construction\Game;

use App\Game;

class GameFactory
{
    /**
     * @param $title
     * @param $linkTitle
     * @param $priceEshop
     * @param $players
     * @param null $amazonUkLink
     * @param null $videoUrl
     * @param null $boxartSquareUrl
     * @param null $nintendoPageUrl
     * @param null $eshopEuropeFsId
     * @param null $boxartHeaderImage
     * @return Game
     */
    public static function create(
        $title, $linkTitle, $priceEshop, $players, $amazonUkLink = null, $videoUrl = null,
        $boxartSquareUrl = null, $eshopEuropeFsId = null,
        $boxartHeaderImage = null
    )
    {
        return new Game(
            [
                'title' => $title,
                'link_title' => $linkTitle,
                'price_eshop' => $priceEshop,
                'players' => $players,
                'review_count' => 0,
                'amazon_uk_link' => $amazonUkLink,
                'video_url' => $videoUrl,
                'boxart_square_url' => $boxartSquareUrl,
                'eshop_europe_fs_id' => $eshopEuropeFsId,
                'boxart_header_image' => $boxartHeaderImage,
            ]
        );
    }
}