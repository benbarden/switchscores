<?php

namespace App\Domain\ReviewDraft;

use App\Models\ReviewDraft;

class Repository
{
    /**
     * @param $id
     * @return ReviewDraft
     */
    public function find($id)
    {
        return ReviewDraft::find($id);
    }

    /**
     * @param $itemUrl
     * @return ReviewDraft
     */
    public function getByItemUrl($itemUrl)
    {
        return ReviewDraft::where('item_url', $itemUrl)->first();
    }

    public function countUnprocessed()
    {
        return ReviewDraft::whereNull('process_status')->orderBy('id', 'asc')->count();
    }

    public function getUnprocessed()
    {
        return ReviewDraft::whereNull('process_status')->orderBy('id', 'asc')->get();
    }

    public function getReadyForProcessing()
    {
        return ReviewDraft::whereNotNull('game_id')
            ->whereNotNull('item_url')
            ->whereNotNull('site_id')
            ->whereNotNull('item_date')
            ->whereNotNull('item_rating')
            ->whereNull('process_status')
            ->get();
    }

}