<?php

namespace App\Construction\Game;


use App\Game;
use Illuminate\Http\Request;

class GameDirector
{
    /**
     * @var GameBuilder
     */
    private $builder;

    public function setBuilder(GameBuilder $builder): void
    {
        $this->builder = $builder;
    }

    public function buildNewGame($params): void
    {
        $this->buildGame($params);
        $this->builder->setReviewCount(0);
    }

    public function buildExistingGame(Game $game, $params): void
    {
        $this->builder->setGame($game);
        $this->buildGame($params);
    }

    public function buildGame($params): void
    {
        if (array_key_exists('title', $params)) {
            $this->builder->setTitle($params['title']);
        }
        if (array_key_exists('link_title', $params)) {
            $this->builder->setLinkTitle($params['link_title']);
        }
        if (array_key_exists('price_eshop', $params)) {
            $this->builder->setPriceEshop($params['price_eshop']);
        }
        if (array_key_exists('players', $params)) {
            $this->builder->setPlayers($params['players']);
        }
        if (array_key_exists('developer', $params)) {
            $this->builder->setDeveloper($params['developer']);
        }
        if (array_key_exists('publisher', $params)) {
            $this->builder->setPublisher($params['publisher']);
        }
        if (array_key_exists('amazon_uk_link', $params)) {
            $this->builder->setAmazonUkLink($params['amazon_uk_link']);
        }
        if (array_key_exists('overview', $params)) {
            $this->builder->setOverview($params['overview']);
        }
        if (array_key_exists('media_folder', $params)) {
            $this->builder->setMediaFolder($params['media_folder']);
        }
        if (array_key_exists('video_url', $params)) {
            $this->builder->setVideoUrl($params['video_url']);
        }
        if (array_key_exists('video_header_text', $params)) {
            $this->builder->setVideoHeaderText($params['video_header_text']);
        }
        if (array_key_exists('boxart_square_url', $params)) {
            $this->builder->setBoxartSquareUrl($params['boxart_square_url']);
        }
        if (array_key_exists('eshop_europe_fs_id', $params)) {
            $this->builder->setEshopEuropeFsId($params['eshop_europe_fs_id']);
        }
        if (array_key_exists('boxart_header_image', $params)) {
            $this->builder->setBoxartHeaderImage($params['boxart_header_image']);
        }
        if (array_key_exists('primary_type_id', $params)) {
            $this->builder->setPrimaryTypeId($params['primary_type_id']);
        }
        if (array_key_exists('series_id', $params)) {
            $this->builder->setGameSeriesId($params['series_id']);
        }
    }
}