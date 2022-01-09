<?php

namespace App\Domain\ReviewLink;

use App\Models\ReviewLink;

class Builder
{
    /**
     * @var ReviewLink
     */
    private $reviewLink;

    public function __construct()
    {
        $this->reset();
    }

    public function reset(): void
    {
        $this->reviewLink = new ReviewLink;
    }

    public function getReviewLink(): ReviewLink
    {
        return $this->reviewLink;
    }

    public function setReviewLink(ReviewLink $reviewLink): void
    {
        $this->reviewLink = $reviewLink;
    }

    public function setGameId($gameId): Builder
    {
        $this->reviewLink->game_id = $gameId;
        return $this;
    }

    public function setSiteId($siteId): Builder
    {
        $this->reviewLink->site_id = $siteId;
        return $this;
    }

    public function setUrl($url): Builder
    {
        $this->reviewLink->url = $url;
        return $this;
    }

    public function setRatingOriginal($ratingOriginal): Builder
    {
        $this->reviewLink->rating_original = $ratingOriginal;
        return $this;
    }

    public function setRatingNormalised($ratingNormalised): Builder
    {
        $this->reviewLink->rating_normalised = $ratingNormalised;
        return $this;
    }

    public function setReviewDate($reviewDate): Builder
    {
        $this->reviewLink->review_date = $reviewDate;
        return $this;
    }

    public function setReviewType($reviewType): Builder
    {
        $this->reviewLink->review_type = $reviewType;
        return $this;
    }

    public function setDesc($desc): Builder
    {
        $this->reviewLink->desc = $desc;
        return $this;
    }

    public function setUserId($userId): Builder
    {
        $this->reviewLink->user_id = $userId;
        return $this;
    }
}