<?php

namespace App\Construction\ReviewSite;


use App\Models\ReviewSite;

class ReviewSiteDirector
{
    /**
     * @var ReviewSiteBuilder
     */
    private $builder;

    public function setBuilder(ReviewSiteBuilder $builder): void
    {
        $this->builder = $builder;
    }

    public function buildNew($params): void
    {
        $this->buildReviewSite($params);
        $this->builder->setStatus(ReviewSite::STATUS_ACTIVE);
        $this->builder->setReviewCount(0);
    }

    public function buildExisting(ReviewSite $reviewSite, $params): void
    {
        $this->builder->setReviewSite($reviewSite);
        $this->buildReviewSite($params);
    }

    public function buildReviewSite($params): void
    {
        if (array_key_exists('status', $params)) {
            $this->builder->setStatus($params['status']);
        }
        if (array_key_exists('name', $params)) {
            $this->builder->setName($params['name']);
        }
        if (array_key_exists('link_title', $params)) {
            $this->builder->setLinkTitle($params['link_title']);
        }
        if (array_key_exists('website_url', $params)) {
            $this->builder->setWebsiteUrl($params['website_url']);
        }
        if (array_key_exists('twitter_id', $params)) {
            $this->builder->setTwitterId($params['twitter_id']);
        }
        if (array_key_exists('rating_scale', $params)) {
            $this->builder->setRatingScale($params['rating_scale']);
        }
        if (array_key_exists('review_count', $params)) {
            $this->builder->setReviewCount($params['review_count']);
        }
        if (array_key_exists('last_review_date', $params)) {
            $this->builder->setLastReviewDate($params['last_review_date']);
        }
        if (array_key_exists('contact_name', $params)) {
            $this->builder->setContactName($params['contact_name']);
        }
        if (array_key_exists('contact_email', $params)) {
            $this->builder->setContactEmail($params['contact_email']);
        }
        if (array_key_exists('contact_form_link', $params)) {
            $this->builder->setContactFormLink($params['contact_form_link']);
        }
        if (array_key_exists('review_code_regions', $params)) {
            $this->builder->setReviewCodeRegions($params['review_code_regions']);
        }
        if (array_key_exists('review_import_method', $params)) {
            $this->builder->setReviewImportMethod($params['review_import_method']);
        }
        $disableLinks = false;
        if (array_key_exists('disable_links', $params)) {
            if ($params['disable_links'] == "on") {
                $disableLinks = true;
            }
        }
        if ($disableLinks) {
            $this->builder->setDisableLinks(1);
        } else {
            $this->builder->setDisableLinks(null);
        }
    }
}