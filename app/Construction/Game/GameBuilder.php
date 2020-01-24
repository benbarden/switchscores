<?php

namespace App\Construction\Game;

use App\Game;

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

    public function setDeveloper($developer): GameBuilder
    {
        $this->game->developer = $developer;
        return $this;
    }

    public function setPublisher($publisher): GameBuilder
    {
        $this->game->publisher = $publisher;
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

    public function setVideoUrl($videoUrl): GameBuilder
    {
        $this->game->video_url = $videoUrl;
        return $this;
    }

    public function setVideoHeaderText($videoHeaderText): GameBuilder
    {
        $this->game->video_header_text = $videoHeaderText;
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

    public function setPrimaryTypeId($primaryTypeId): GameBuilder
    {
        $this->game->primary_type_id = $primaryTypeId;
        return $this;
    }

    public function setGameSeriesId($gameSeriesId): GameBuilder
    {
        $this->game->series_id = $gameSeriesId;
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
}