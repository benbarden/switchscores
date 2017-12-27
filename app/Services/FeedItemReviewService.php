<?php


namespace App\Services;

use App\FeedItemReview;


class FeedItemReviewService
{
    public function getByItemUrl($itemUrl)
    {
        $feedItemReview = FeedItemReview::
            where('item_url', $itemUrl)
            ->first();
        return $feedItemReview;
    }

    public function getItemsToParse($limit = 25)
    {
        $feedItemReview = FeedItemReview::
            whereNull('parsed')
            ->limit($limit)
            ->get();
        return $feedItemReview;
    }
}