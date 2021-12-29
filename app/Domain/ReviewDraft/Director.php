<?php

namespace App\Domain\ReviewDraft;

use App\Models\ReviewDraft;

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
        $this->builder->getReviewDraft()->save();
    }

    public function getReviewDraft()
    {
        return $this->builder->getReviewDraft();
    }

    public function buildNewManual($params): void
    {
        $this->builder->setSource(ReviewDraft::SOURCE_MANUAL);
        $this->buildReviewDraft($params);
    }

    public function buildNewFeed($params): void
    {
        $this->builder->setSource(ReviewDraft::SOURCE_FEED);
        $this->buildReviewDraft($params);
    }

    public function buildNewScraper($params): void
    {
        $this->builder->setSource(ReviewDraft::SOURCE_SCRAPER);
        $this->buildReviewDraft($params);
    }

    public function buildExisting(ReviewDraft $reviewDraft, $params): void
    {
        $this->builder->setReviewDraft($reviewDraft);
        $this->buildReviewDraft($params);
    }

    public function buildReviewDraft($params): void
    {
        if (array_key_exists('site_id', $params)) {
            $this->builder->setSiteId($params['site_id']);
        }
        if (array_key_exists('user_id', $params)) {
            $this->builder->setUserId($params['user_id']);
        }
        if (array_key_exists('game_id', $params)) {
            $this->builder->setGameId($params['game_id']);
        }
        if (array_key_exists('item_url', $params)) {
            $this->builder->setItemUrl($params['item_url']);
        }
        if (array_key_exists('item_title', $params)) {
            $this->builder->setItemTitle($params['item_title']);
        }
        if (array_key_exists('parsed_title', $params)) {
            $this->builder->setParsedTitle($params['parsed_title']);
        }
        if (array_key_exists('item_date', $params)) {
            $this->builder->setItemDate($params['item_date']);
        }
        if (array_key_exists('item_rating', $params)) {
            $this->builder->setItemRating($params['item_rating']);
        }
        if (array_key_exists('parse_status', $params)) {
            $this->builder->setParseStatus($params['parse_status']);
        }
        if (array_key_exists('process_status', $params)) {
            $this->builder->setProcessStatus($params['process_status']);
        }
        if (array_key_exists('review_link_id', $params)) {
            $this->builder->setReviewLinkId($params['review_link_id']);
        }
    }
}
