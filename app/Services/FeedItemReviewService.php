<?php


namespace App\Services;

use App\FeedItemReview;


class FeedItemReviewService
{
    public function edit(
        FeedItemReview $feedItemReview,
        $siteId, $gameId, $itemRating
    )
    {
        $feedItemReview->site_id = $siteId;
        $feedItemReview->game_id = $gameId;
        $feedItemReview->item_rating = $itemRating;
        $feedItemReview->save();
    }

    public function find($id)
    {
        $feedItemReview = FeedItemReview::find($id);
        return $feedItemReview;
    }

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

    public function getAll()
    {
        $feedItemReview = FeedItemReview::orderBy('id', 'desc')->get();
        return $feedItemReview;
    }

    public function getUnprocessed($limit = null)
    {
        $limit = (int) $limit;
        if ($limit) {
            $feedItemReview = FeedItemReview::
                whereNull('processed')
                ->orderBy('id', 'asc')
                ->limit($limit)
                ->get();
        } else {
            $feedItemReview = FeedItemReview::
                whereNull('processed')
                ->orderBy('id', 'asc')
                ->get();
        }
        return $feedItemReview;
    }

    public function getProcessed()
    {
        $feedItemReview = FeedItemReview::
            where('processed', 1)
            ->orderBy('id', 'desc')
            ->get();
        return $feedItemReview;
    }
}