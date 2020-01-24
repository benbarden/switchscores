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
        if (array_key_exists('eu_release_date', $params)) {
            $this->builder->setEuReleaseDate($params['eu_release_date']);
            $releaseYear = $this->buildReleaseYear($params['eu_release_date']);
            $this->builder->setReleaseYear($releaseYear);
        }
        if (array_key_exists('us_release_date', $params)) {
            $this->builder->setUsReleaseDate($params['us_release_date']);
        }
        if (array_key_exists('jp_release_date', $params)) {
            $this->builder->setJpReleaseDate($params['jp_release_date']);
        }
        if (array_key_exists('eu_is_released', $params)) {
            $isReleased = $params['eu_is_released'] == 'on' ? 1 : 0;
            $this->builder->setEuIsReleased($isReleased);
        } else {
            $this->builder->setEuIsReleased(0);
        }
        if (array_key_exists('price_eshop', $params)) {
            if ($params['price_eshop'] != '') {
                $this->builder->setPriceEshop($params['price_eshop']);
            }
        }
        if (array_key_exists('players', $params)) {
            if ($params['players'] != '') {
                $this->builder->setPlayers($params['players']);
            }
        }
        if (array_key_exists('eu_released_on', $params)) {
            if ($params['eu_released_on'] != '') {
                $this->builder->setEuReleasedOn($params['eu_released_on']);
            }
        }
        if (array_key_exists('developer', $params)) {
            if ($params['developer'] != '') {
                $this->builder->setDeveloper($params['developer']);
            }
        }
        if (array_key_exists('publisher', $params)) {
            if ($params['publisher'] != '') {
                $this->builder->setPublisher($params['publisher']);
            }
        }
        if (array_key_exists('amazon_uk_link', $params)) {
            if ($params['amazon_uk_link'] != '') {
                $this->builder->setAmazonUkLink($params['amazon_uk_link']);
            }
        }
        if (array_key_exists('video_url', $params)) {
            if ($params['video_url'] != '') {
                $this->builder->setVideoUrl($params['video_url']);
            }
        }
        if (array_key_exists('video_header_text', $params)) {
            if ($params['video_header_text'] != '') {
                $this->builder->setVideoHeaderText($params['video_header_text']);
            }
        }
        if (array_key_exists('boxart_square_url', $params)) {
            if ($params['boxart_square_url'] != '') {
                $this->builder->setBoxartSquareUrl($params['boxart_square_url']);
            }
        }
        if (array_key_exists('eshop_europe_fs_id', $params)) {
            if ($params['eshop_europe_fs_id'] != '') {
                $this->builder->setEshopEuropeFsId($params['eshop_europe_fs_id']);
            }
        }
        if (array_key_exists('boxart_header_image', $params)) {
            if ($params['boxart_header_image'] != '') {
                $this->builder->setBoxartHeaderImage($params['boxart_header_image']);
            }
        }
        if (array_key_exists('primary_type_id', $params)) {
            if ($params['primary_type_id'] != '') {
                $this->builder->setPrimaryTypeId($params['primary_type_id']);
            }
        }
        if (array_key_exists('series_id', $params)) {
            if ($params['series_id'] != '') {
                $this->builder->setGameSeriesId($params['series_id']);
            }
        }
    }

    public function buildReleaseYear($releaseDate): string
    {
        if ($releaseDate) {
            $releaseDateObject = new \DateTime($releaseDate);
            $releaseYear = $releaseDateObject->format('Y');
        } else {
            $releaseYear = '';
        }

        return $releaseYear;
    }
}