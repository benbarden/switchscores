<?php

namespace App\Domain\ReviewFeedItem;

use App\ReviewFeedItem;

class Repository
{
    /**
     * @param $id
     * @return ReviewFeedItem
     */
    public function find($id)
    {
        return ReviewFeedItem::find($id);
    }

    /**
     * @param $itemUrl
     * @return ReviewFeedItem
     */
    public function getByItemUrl($itemUrl)
    {
        return ReviewFeedItem::where('item_url', $itemUrl)->first();
    }

}