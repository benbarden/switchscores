<?php


namespace App\Services;

use App\ReviewFeedItem;

use Illuminate\Support\Facades\DB;

class ReviewFeedItemService
{
    public function edit(
        ReviewFeedItem $reviewFeedItem,
        $siteId, $gameId, $itemRating, $processed, $processStatus
    )
    {
        $isProcessed = $processed == 'on' ? 1 : null;

        if (!$processStatus) {
            $processStatus = null;
        }

        $reviewFeedItem->site_id = $siteId;
        $reviewFeedItem->game_id = $gameId;
        $reviewFeedItem->item_rating = $itemRating;
        $reviewFeedItem->processed = $isProcessed;
        $reviewFeedItem->process_status = $processStatus;
        $reviewFeedItem->save();
    }

    public function find($id)
    {
        return ReviewFeedItem::find($id);
    }

    public function getByItemUrl($itemUrl)
    {
        return ReviewFeedItem::where('item_url', $itemUrl)->first();
    }

    public function getItemsToParse($limit = null)
    {
        $limit = (int) $limit;
        if ($limit) {
            $reviewFeedItem = ReviewFeedItem::
                whereNull('parsed')
                ->orderBy('item_date', 'asc')
                ->limit($limit)
                ->get();
        } else {
            $reviewFeedItem = ReviewFeedItem::
                whereNull('parsed')
                ->orderBy('id', 'asc')
                ->get();
        }

        return $reviewFeedItem;
    }

    public function getAll()
    {
        return ReviewFeedItem::orderBy('id', 'desc')->get();
    }

    public function getUnprocessed()
    {
        return ReviewFeedItem::whereNull('processed')->orderBy('id', 'asc')->get();
    }

    public function getProcessed($limit = 25)
    {
        $reviewFeedItem = ReviewFeedItem::where('processed', 1)->orderBy('id', 'desc');

        if ($limit) {
            $reviewFeedItem = $reviewFeedItem->limit($limit);
        }

        $reviewFeedItem = $reviewFeedItem->get();
        return $reviewFeedItem;
    }

    public function getByProcessStatus($status)
    {
        return ReviewFeedItem::where('process_status', $status)->orderBy('id', 'desc')->get();
    }

    public function getByImportId($importId)
    {
        return ReviewFeedItem::where('import_id', $importId)->orderBy('id', 'desc')->get();
    }

    public function getProcessStatusStats()
    {
        return DB::select('
            select process_status, count(*) AS count
            from review_feed_items
            where process_status is not null
            group by process_status
        ');
    }
}