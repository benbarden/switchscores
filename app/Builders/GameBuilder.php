<?php

namespace App\Builders;

use App\Game;

class GameBuilder
{
    private $title;
    private $linkTitle;
    private $priceEshop;
    private $players;
    private $overview;
    private $developer;
    private $publisher;
    private $mediaFolder;
    private $reviewCount;
    private $amazonUkLink;
    private $videoUrl;
    private $boxartSquareUrl;
    private $nintendoPageUrl;
    private $eshopEuropeFsId;
    private $boxartHeaderImage;

    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    public function setLinkTitle($linkTitle)
    {
        $this->linkTitle = $linkTitle;
        return $this;
    }

    public function setPriceEshop($priceEshop)
    {
        $this->priceEshop = $priceEshop;
        return $this;
    }

    public function setPlayers($players)
    {
        $this->players = $players;
        return $this;
    }

    public function setOverview($overview)
    {
        $this->overview = $overview;
        return $this;
    }

    public function setDeveloper($developer)
    {
        $this->developer = $developer;
        return $this;
    }

    public function setPublisher($publisher)
    {
        $this->publisher = $publisher;
        return $this;
    }

    public function setMediaFolder($mediaFolder)
    {
        $this->mediaFolder = $mediaFolder;
        return $this;
    }

    public function setReviewCount($reviewCount)
    {
        $this->reviewCount = $reviewCount;
        return $this;
    }

    public function setAmazonUkLink($amazonUkLink)
    {
        $this->amazonUkLink = $amazonUkLink;
        return $this;
    }

    public function setVideoUrl($videoUrl)
    {
        $this->videoUrl = $videoUrl;
        return $this;
    }

    public function setBoxartSquareUrl($boxartSquareUrl)
    {
        $this->boxartSquareUrl = $boxartSquareUrl;
        return $this;
    }

    public function setNintendoPageUrl($nintendoPageUrl)
    {
        $this->nintendoPageUrl = $nintendoPageUrl;
        return $this;
    }

    public function setEshopEuropeFsId($eshopEuropeFsId)
    {
        $this->eshopEuropeFsId = $eshopEuropeFsId;
        return $this;
    }

    public function setBoxartHeaderImage($boxartHeaderImage)
    {
        $this->boxartHeaderImage = $boxartHeaderImage;
        return $this;
    }

    public function build(): Game
    {
        return new Game([
            'title' => $this->title,
            'link_title' => $this->linkTitle,
            'price_eshop' => $this->priceEshop,
            'players' => $this->players,
            'overview' => $this->overview,
            'developer' => $this->developer,
            'publisher' => $this->publisher,
            'media_folder' => $this->mediaFolder,
            'review_count' => 0,
            'amazon_uk_link' => $this->amazonUkLink,
            'video_url' => $this->videoUrl,
            'boxart_square_url' => $this->boxartSquareUrl,
            'nintendo_page_url' => $this->nintendoPageUrl,
            'eshop_europe_fs_id' => $this->eshopEuropeFsId,
            'boxart_header_image' => $this->boxartHeaderImage,
        ]);
    }
}