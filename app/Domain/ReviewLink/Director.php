<?php

namespace App\Domain\ReviewLink;

use App\ReviewLink;

class Director
{
    /**
     * @var Builder
     */
    private $builder;

    public function __construct(Builder $builder)
    {
        $this->builder = $builder;
    }

    public function save()
    {
        $this->builder->getReviewLink()->save();
    }

    public function getReviewLink()
    {
        return $this->builder->getReviewLink();
    }

    public function buildNewImported($params): void
    {
        $this->builder->setReviewType(ReviewLink::TYPE_IMPORTED);
        $this->buildReviewLink($params);
    }

    public function buildNewManual($params): void
    {
        $this->builder->setReviewType(ReviewLink::TYPE_MANUAL);
        $this->buildReviewLink($params);
    }

    public function buildNewPartner($params): void
    {
        $this->builder->setReviewType(ReviewLink::TYPE_PARTNER);
        $this->buildReviewLink($params);
    }

    public function buildExisting(ReviewLink $reviewLink, $params): void
    {
        $this->builder->setReviewLink($reviewLink);
        $this->buildReviewLink($params);
    }

    public function buildReviewLink($params): void
    {
        if (array_key_exists('game_id', $params)) {
            $this->builder->setGameId($params['game_id']);
        }
        if (array_key_exists('site_id', $params)) {
            $this->builder->setSiteId($params['site_id']);
        }
        if (array_key_exists('url', $params)) {
            $this->builder->setUrl($params['url']);
        }
        if (array_key_exists('rating_original', $params)) {
            $this->builder->setRatingOriginal($params['rating_original']);
        }
        if (array_key_exists('rating_normalised', $params)) {
            $this->builder->setRatingNormalised($params['rating_normalised']);
        }
        if (array_key_exists('review_date', $params)) {
            $this->builder->setReviewDate($params['review_date']);
        }
        if (array_key_exists('review_type', $params)) {
            $this->builder->setReviewType($params['review_type']);
        }
        if (array_key_exists('desc', $params)) {
            $this->builder->setDesc($params['desc']);
        }
        if (array_key_exists('user_id', $params)) {
            $this->builder->setUserId($params['user_id']);
        }
    }
}
