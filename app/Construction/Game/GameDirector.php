<?php

namespace App\Construction\Game;


use App\Models\Game;

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
        $this->builder->setAddedBatchDateToToday();
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
        if (array_key_exists('console_id', $params)) {
            $this->builder->setConsoleId($params['console_id']);
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
            $this->builder->setPriceEshop($params['price_eshop']);
        }
        if (array_key_exists('price_eshop_discounted', $params)) {
            $this->builder->setPriceEshopDiscounted($params['price_eshop_discounted']);
        }
        if (array_key_exists('price_eshop_discount_pc', $params)) {
            $this->builder->setPriceEshopDiscountPc($params['price_eshop_discount_pc']);
        }
        if (array_key_exists('players', $params)) {
            $this->builder->setPlayers($params['players']);
        }
        if (array_key_exists('eu_released_on', $params)) {
            $this->builder->setEuReleasedOn($params['eu_released_on']);
        }
        if (array_key_exists('amazon_uk_link', $params)) {
            $this->builder->setAmazonUkLink($params['amazon_uk_link']);
        }
        if (array_key_exists('amazon_us_link', $params)) {
            $this->builder->setAmazonUsLink($params['amazon_us_link']);
        }
        if (array_key_exists('amazon_uk_status', $params)) {
            $this->builder->setAmazonUkStatus($params['amazon_uk_status']);
        }
        if (array_key_exists('amazon_us_status', $params)) {
            $this->builder->setAmazonUsStatus($params['amazon_us_status']);
        }
        if (array_key_exists('nintendo_store_url_override', $params)) {
            $this->builder->setNintendoStoreUrlOverride($params['nintendo_store_url_override']);
        }
        if (array_key_exists('video_url', $params)) {
            $this->builder->setVideoUrl($params['video_url']);
        }
        if (array_key_exists('video_type', $params)) {
            $this->builder->setVideoType($params['video_type']);
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
        if (array_key_exists('image_square', $params)) {
            $this->builder->setImageSquare($params['image_square']);
        }
        if (array_key_exists('image_header', $params)) {
            $this->builder->setImageHeader($params['image_header']);
        }
        if (array_key_exists('category_id', $params)) {
            $this->builder->setCategoryId($params['category_id']);
        }
        if (array_key_exists('series_id', $params)) {
            $this->builder->setGameSeriesId($params['series_id']);
        }
        if (array_key_exists('collection_id', $params)) {
            $this->builder->setGameCollectionId($params['collection_id']);
        }
        if (array_key_exists('format_digital', $params)) {
            $this->builder->setFormatDigital($params['format_digital']);
        }
        if (array_key_exists('format_physical', $params)) {
            $this->builder->setFormatPhysical($params['format_physical']);
        }
        if (array_key_exists('format_dlc', $params)) {
            $this->builder->setFormatDLC($params['format_dlc']);
        }
        if (array_key_exists('format_demo', $params)) {
            $this->builder->setFormatDemo($params['format_demo']);
        }
        if (array_key_exists('eshop_europe_order', $params)) {
            $this->builder->setEshopEuropeOrder($params['eshop_europe_order']);
        }
        if (array_key_exists('is_low_quality', $params)) {
            $isLowQuality = $params['is_low_quality'] == 'on' ? 1 : 0;
            $this->builder->setIsLowQuality($isLowQuality);
        } else {
            $this->builder->setIsLowQuality(0);
        }
        if (array_key_exists('category_verification', $params)) {
            $this->builder->setCategoryVerification($params['category_verification']);
        }
        if (array_key_exists('tags_verification', $params)) {
            $this->builder->setTagsVerification($params['tags_verification']);
        }
        if (array_key_exists('taxonomy_needs_review', $params)) {
            $this->builder->setTaxonomyNeedsReview($params['taxonomy_needs_review']);
        }
        if (array_key_exists('packshot_square_url_override', $params)) {
            $this->builder->setPackshotSquareUrlOverride($params['packshot_square_url_override']);
        }
        if (array_key_exists('one_to_watch', $params)) {
            $oneToWatch = $params['one_to_watch'] == 'on' ? 1 : 0;
            $this->builder->setOneToWatch($oneToWatch);
        } else {
            $this->builder->setOneToWatch(0);
        }
        if (array_key_exists('game_description', $params)) {
            $this->builder->setGameDescription($params['game_description']);
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