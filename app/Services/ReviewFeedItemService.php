<?php


namespace App\Services;

use App\Models\ReviewFeedItem;
use Illuminate\Support\Facades\DB;

class ReviewFeedItemService
{
    private $processOptionsSuccess = [
        'Review created',
    ];

    private $processOptionsFailure = [
        'Bundle',
        'DLC or special edition',
        'Duplicate',
        'Historic review',
        'Multiple reviews',
        'No score',
        'Non-review content',
        'Not a game review',
        'Not in database',
        'Page not found',
        'Review for another platform',
        'Review pre-dates Switch version',
    ];

    public function add($siteId, $gameId, $itemUrl, $itemTitle, $itemDate, $itemRating)
    {
        return ReviewFeedItem::create([
            'site_id' => $siteId,
            'game_id' => $gameId,
            'item_url' => $itemUrl,
            'item_title' => $itemTitle,
            'item_date' => $itemDate,
            'item_rating' => $itemRating,
            'load_status' => 'Loaded OK',
            'parse_status' => 'Manually linked by reviewer',
            'parsed' => 1,
        ]);
    }

    public function edit(
        ReviewFeedItem $reviewFeedItem, $siteId, $gameId, $itemRating, $processStatus
    )
    {
        if ($processStatus) {
            $isProcessed = 1;
        } else {
            $isProcessed = null;
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
                ->whereNull('processed')
                ->orderBy('item_date', 'asc')
                ->limit($limit)
                ->get();
        } else {
            $reviewFeedItem = ReviewFeedItem::
                whereNull('parsed')
                ->whereNull('processed')
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

    public function getAllBySite($siteId, $limit)
    {
        return ReviewFeedItem::where('site_id', $siteId)->orderBy('id', 'desc')->limit($limit)->get();
    }

    public function getUnprocessedBySite($siteId, $limit = null)
    {
        $feedItems = ReviewFeedItem::whereNull('processed')
            ->where('site_id', $siteId)
            ->orderBy('id', 'asc');

        if ($limit) {
            $feedItems = $feedItems->limit($limit);
        }

        return $feedItems->get();
    }

    public function getSuccessBySite($siteId, $limit = 5)
    {
        return ReviewFeedItem::
            where('process_status', 'Review created')
            ->where('site_id', $siteId)
            ->orderBy('id', 'asc')
            ->limit($limit)
            ->get();
    }

    public function getFailedBySite($siteId, $limit = 5)
    {
        return ReviewFeedItem::
            whereNotNull('process_status')
            ->where('process_status', '!=', 'Review created')
            ->where('site_id', $siteId)
            ->orderBy('id', 'asc')
            ->limit($limit)
            ->get();
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

    public function getByProcessStatusAndSite($status, $siteId)
    {
        return ReviewFeedItem::where('process_status', $status)->where('site_id', $siteId)->orderBy('id', 'desc')->get();
    }

    public function getByParseStatusAndSite($status, $siteId, $limit = 100)
    {
        $feedItems = ReviewFeedItem::
            where('parse_status', $status)
            ->where('process_status', 'Review created')
            ->where('site_id', $siteId)->orderBy('id', 'desc');
        if ($limit) {
            $feedItems = $feedItems->limit($limit);
        }
        return $feedItems->get();
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

    public function getFailedImportStatsBySite($siteId)
    {
        $failedStatuses = implode("', '", $this->getProcessOptionsFailure());
        $statsList = DB::select('
            select process_status, count(*) AS count
            from review_feed_items
            where process_status in (\''.$failedStatuses.'\')
            and site_id = ?
            group by process_status
        ', [$siteId]);

        return $statsList;
    }

    public function getProcessOptionsSuccess()
    {
        return $this->processOptionsSuccess;
    }

    public function getProcessOptionsFailure()
    {
        return $this->processOptionsFailure;
    }

    public function getSuccessFailStatsBySite($siteId)
    {
        $successFailStats = DB::select("
            SELECT
            CASE
            WHEN process_status = 'Review created' THEN 'Success'
            WHEN process_status IS NOT NULL THEN 'Fail'
            ELSE 'Pending'
            END AS import_success,
            count(*) AS count
            FROM review_feed_items
            WHERE site_id = ?
            GROUP BY import_success
            ORDER BY process_status DESC
        ", [$siteId]);

        return $successFailStats;
    }


    public function getParseStatusStatsBySite($siteId)
    {
        $parseStatusStats = DB::select("
            SELECT parse_status, count(*) AS count
            FROM review_feed_items
            WHERE site_id = ?
            AND process_status = 'Review created'
            GROUP BY parse_status
            ORDER BY parse_status
        ", [$siteId]);

        return $parseStatusStats;
    }
}