<?php

namespace App\Domain\ReviewFeedItem;

use App\Models\ReviewFeedItem;

class Repository
{
    /**
     * @param $id
     * @return \App\Models\ReviewFeedItem
     */
    public function find($id)
    {
        return ReviewFeedItem::find($id);
    }

    /**
     * @param $itemUrl
     * @return \App\Models\ReviewFeedItem
     */
    public function getByItemUrl($itemUrl)
    {
        return ReviewFeedItem::where('item_url', $itemUrl)->first();
    }

}