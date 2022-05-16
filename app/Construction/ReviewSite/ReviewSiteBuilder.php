<?php

namespace App\Construction\ReviewSite;


use App\Models\ReviewSite;

class ReviewSiteBuilder
{
    /**
     * @var ReviewSite
     */
    private $reviewSite;

    public function __construct()
    {
        $this->reset();
    }

    public function reset(): void
    {
        $this->reviewSite = new ReviewSite();
    }

    public function getReviewSite(): ReviewSite
    {
        return $this->reviewSite;
    }

    public function setReviewSite(ReviewSite $reviewSite): void
    {
        $this->reviewSite = $reviewSite;
    }

    public function setStatus($value): ReviewSiteBuilder
    {
        $this->reviewSite->status = $value;
        return $this;
    }

    public function setName($value): ReviewSiteBuilder
    {
        $this->reviewSite->name = $value;
        return $this;
    }

    public function setLinkTitle($value): ReviewSiteBuilder
    {
        $this->reviewSite->link_title = $value;
        return $this;
    }

    public function setWebsiteUrl($value): ReviewSiteBuilder
    {
        $this->reviewSite->website_url = $value;
        return $this;
    }

    public function setTwitterId($value): ReviewSiteBuilder
    {
        $this->reviewSite->twitter_id = $value;
        return $this;
    }

    public function setRatingScale($value): ReviewSiteBuilder
    {
        $this->reviewSite->rating_scale = $value;
        return $this;
    }

    public function setReviewCount($value): ReviewSiteBuilder
    {
        $this->reviewSite->review_count = $value;
        return $this;
    }

    public function setLastReviewDate($value): ReviewSiteBuilder
    {
        $this->reviewSite->last_review_date = $value;
        return $this;
    }

    public function setContactName($value): ReviewSiteBuilder
    {
        $this->reviewSite->contact_name = $value;
        return $this;
    }

    public function setContactEmail($value): ReviewSiteBuilder
    {
        $this->reviewSite->contact_email = $value;
        return $this;
    }

    public function setContactFormLink($value): ReviewSiteBuilder
    {
        $this->reviewSite->contact_form_link = $value;
        return $this;
    }

    public function setReviewCodeRegions($value): ReviewSiteBuilder
    {
        $this->reviewSite->review_code_regions = $value;
        return $this;
    }

    public function setReviewImportMethod($value): ReviewSiteBuilder
    {
        $this->reviewSite->review_import_method = $value;
        return $this;
    }

}