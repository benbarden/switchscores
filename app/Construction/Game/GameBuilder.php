<?php

namespace App\Construction\Game;

use App\Models\Game;

class GameBuilder
{
    /**
     * @var Game
     */
    private $game;

    public function __construct()
    {
        $this->reset();
    }

    public function reset(): void
    {
        $this->game = new Game;
    }

    public function getGame(): Game
    {
        return $this->game;
    }

    public function setGame(Game $game): void
    {
        $this->game = $game;
    }

    public function setTitle($title): GameBuilder
    {
        $this->game->title = $title;
        return $this;
    }

    public function setLinkTitle($linkTitle): GameBuilder
    {
        $this->game->link_title = $linkTitle;
        return $this;
    }

    public function setEuReleaseDate($releaseDate): GameBuilder
    {
        $this->game->eu_release_date = $releaseDate;
        return $this;
    }

    public function setUsReleaseDate($releaseDate): GameBuilder
    {
        $this->game->us_release_date = $releaseDate;
        return $this;
    }

    public function setJpReleaseDate($releaseDate): GameBuilder
    {
        $this->game->jp_release_date = $releaseDate;
        return $this;
    }

    public function setEuIsReleased($isReleased): GameBuilder
    {
        $this->game->eu_is_released = $isReleased;
        return $this;
    }

    public function setReleaseYear($releaseYear): GameBuilder
    {
        $this->game->release_year = $releaseYear;
        return $this;
    }

    public function setPriceEshop($priceEshop): GameBuilder
    {
        $this->game->price_eshop = $priceEshop;
        return $this;
    }

    public function setPriceEshopDiscounted($priceEshopDiscounted): GameBuilder
    {
        $this->game->price_eshop_discounted = $priceEshopDiscounted;
        return $this;
    }

    public function setPriceEshopDiscountPc($priceEshopDiscountPc): GameBuilder
    {
        $this->game->price_eshop_discount_pc = $priceEshopDiscountPc;
        return $this;
    }

    public function setPlayers($players): GameBuilder
    {
        $this->game->players = $players;
        return $this;
    }

    public function setEuReleasedOn($euReleasedOn): GameBuilder
    {
        $this->game->eu_released_on = $euReleasedOn;
        return $this;
    }

    public function setReviewCount($reviewCount): GameBuilder
    {
        $this->game->review_count = $reviewCount;
        return $this;
    }

    public function setAmazonUkLink($amazonUkLink): GameBuilder
    {
        $this->game->amazon_uk_link = $amazonUkLink;
        return $this;
    }

    public function setAmazonUsLink($amazonUsLink): GameBuilder
    {
        $this->game->amazon_us_link = $amazonUsLink;
        return $this;
    }

    public function setNintendoStoreUrlOverride($storeUrl): GameBuilder
    {
        $this->game->nintendo_store_url_override = $storeUrl;
        return $this;
    }

    public function setVideoUrl($videoUrl): GameBuilder
    {
        $this->game->video_url = $videoUrl;
        return $this;
    }

    public function setVideoType($videoType): GameBuilder
    {
        $this->game->video_type = $videoType;
        return $this;
    }

    public function setBoxartSquareUrl($boxartSquareUrl): GameBuilder
    {
        $this->game->boxart_square_url = $boxartSquareUrl;
        return $this;
    }

    public function setEshopEuropeFsId($eshopEuropeFsId): GameBuilder
    {
        $this->game->eshop_europe_fs_id = $eshopEuropeFsId;
        return $this;
    }

    public function setBoxartHeaderImage($boxartHeaderImage): GameBuilder
    {
        $this->game->boxart_header_image = $boxartHeaderImage;
        return $this;
    }

    public function setImageSquare($imageSquare): GameBuilder
    {
        $this->game->image_square = $imageSquare;
        return $this;
    }

    public function setImageHeader($imageHeader): GameBuilder
    {
        $this->game->image_header = $imageHeader;
        return $this;
    }

    public function setCategoryId($categoryId): GameBuilder
    {
        $this->game->category_id = $categoryId;
        return $this;
    }

    public function setGameSeriesId($gameSeriesId): GameBuilder
    {
        $this->game->series_id = $gameSeriesId;
        return $this;
    }

    public function setGameCollectionId($gameCollectionId): GameBuilder
    {
        $this->game->collection_id = $gameCollectionId;
        return $this;
    }

    public function setRatingAvg($ratingAvg): GameBuilder
    {
        $this->game->rating_avg = $ratingAvg;
        return $this;
    }

    public function setGameRank($gameRank): GameBuilder
    {
        $this->game->game_rank = $gameRank;
        return $this;
    }

    public function setFormatDigital($format): GameBuilder
    {
        $this->game->format_digital = $format;
        return $this;
    }

    public function setFormatPhysical($format): GameBuilder
    {
        $this->game->format_physical = $format;
        return $this;
    }

    public function setFormatDLC($format): GameBuilder
    {
        $this->game->format_dlc = $format;
        return $this;
    }

    public function setFormatDemo($format): GameBuilder
    {
        $this->game->format_demo = $format;
        return $this;
    }

    public function setEshopEuropeOrder($eshopOrder): GameBuilder
    {
        $this->game->eshop_europe_order = $eshopOrder;
        return $this;
    }

    public function setIsLowQuality($isLowQuality): GameBuilder
    {
        $this->game->is_low_quality = $isLowQuality;
        return $this;
    }

    public function setPackshotSquareUrlOverride($squareUrlOverride): GameBuilder
    {
        $this->game->packshot_square_url_override = $squareUrlOverride;
        return $this;
    }
}