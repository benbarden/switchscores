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
     * @param $excludeId
     * @return mixed
     */
    public function getByItemUrl($itemUrl, $excludeId = null)
    {
        if ($excludeId) {
            return ReviewDraft::where('item_url', $itemUrl)->where('id', '<>', $excludeId)->first();
        } else {
            return ReviewDraft::where('item_url', $itemUrl)->first();
        }
    }

    public function countUnprocessed()
    {
        return ReviewDraft::whereNull('process_status')->orderBy('id', 'asc')->count();
    }

    public function getUnprocessed()
    {
        return ReviewDraft::whereNull('process_status')->orderBy('id', 'asc')->get();
    }

    public function getUnparsed()
    {
        return ReviewDraft::whereNull('process_status')->whereNull('parse_status')->orderBy('id', 'asc')->get();
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

    // Reviewers dashboard

    public function getUnprocessedBySite($siteId)
    {
        return ReviewDraft::whereNull('process_status')->where('site_id', $siteId)->orderBy('id', 'asc')->get();
    }

    public function getSuccessBySite($siteId, $limit = 5)
    {
        return ReviewDraft::where('process_status', ReviewDraft::PROCESS_SUCCESS_REVIEW_CREATED)
            ->where('site_id', $siteId)->orderBy('id', 'desc')->limit($limit)->get();
    }

    public function getFailedBySite($siteId, $limit = 5)
    {
        return ReviewDraft::whereNotNull('process_status')
            ->where('process_status', '<>', ReviewDraft::PROCESS_SUCCESS_REVIEW_CREATED)
            ->where('site_id', $siteId)->orderBy('id', 'desc')->limit($limit)->get();
    }
}