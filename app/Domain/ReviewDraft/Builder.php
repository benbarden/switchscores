<?php

namespace App\Domain\ReviewDraft;

use App\Models\ReviewDraft;

class Builder
{
    /**
     * @var ReviewDraft
     */
    private $reviewDraft;

    public function __construct()
    {
        $this->reset();
    }

    public function reset(): void
    {
        $this->reviewDraft = new ReviewDraft;
    }

    public function getReviewDraft(): ReviewDraft
    {
        return $this->reviewDraft;
    }

    public function setReviewDraft(ReviewDraft $reviewDraft): void
    {
        $this->reviewDraft = $reviewDraft;
    }

    public function setSource($source): Builder
    {
        $this->reviewDraft->source = $source;
        return $this;
    }

    public function setSiteId($siteId): Builder
    {
        $this->reviewDraft->site_id = $siteId;
        return $this;
    }

    public function setUserId($userId): Builder
    {
        $this->reviewDraft->user_id = $userId;
        return $this;
    }

    public function setGameId($gameId): Builder
    {
        $this->reviewDraft->game_id = $gameId;
        return $this;
    }

    public function setItemUrl($itemUrl): Builder
    {
        $this->reviewDraft->item_url = $itemUrl;
        return $this;
    }

    public function setItemTitle($itemTitle): Builder
    {
        $this->reviewDraft->item_title = $itemTitle;
        return $this;
    }

    public function setParsedTitle($parsedTitle): Builder
    {
        $this->reviewDraft->parsed_title = $parsedTitle;
        return $this;
    }

    public function setItemDate($itemDate): Builder
    {
        $this->reviewDraft->item_date = $itemDate;
        return $this;
    }

    public function setItemRating($itemRating): Builder
    {
        $this->reviewDraft->item_rating = $itemRating;
        return $this;
    }

    public function setParseStatus($parseStatus): Builder
    {
        $this->reviewDraft->parse_status = $parseStatus;
        return $this;
    }

    public function setProcessStatus($processStatus): Builder
    {
        $this->reviewDraft->process_status = $processStatus;
        return $this;
    }

    public function setReviewLinkId($reviewLinkId): Builder
    {
        $this->reviewDraft->review_link_id = $reviewLinkId;
        return $this;
    }
}