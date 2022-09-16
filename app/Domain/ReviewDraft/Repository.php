<?php

namespace App\Domain\ReviewDraft;

use App\Models\ReviewDraft;
use Illuminate\Support\Facades\DB;

class Repository
{
    private $processOptionsSuccess = [
        ReviewDraft::PROCESS_SUCCESS_REVIEW_CREATED,
    ];

    private $processOptionsFailure = [
        ReviewDraft::PROCESS_FAILURE_BUNDLE,
        ReviewDraft::PROCESS_FAILURE_DLC_OR_SPECIAL_EDITION,
        ReviewDraft::PROCESS_FAILURE_DUPLICATE,
        ReviewDraft::PROCESS_FAILURE_HISTORIC,
        ReviewDraft::PROCESS_FAILURE_MULTIPLE,
        ReviewDraft::PROCESS_FAILURE_NO_SCORE,
        ReviewDraft::PROCESS_FAILURE_NON_REVIEW_CONTENT,
        ReviewDraft::PROCESS_FAILURE_NOT_GAME_REVIEW,
        ReviewDraft::PROCESS_FAILURE_NOT_IN_DB,
        ReviewDraft::PROCESS_FAILURE_PAGE_NOT_FOUND,
        ReviewDraft::PROCESS_FAILURE_REVIEW_FOR_ANOTHER_PLATFORM,
        ReviewDraft::PROCESS_FAILURE_REVIEW_PREDATES_SWITCH_VERSION,
    ];

    public function getProcessOptionsSuccess()
    {
        return $this->processOptionsSuccess;
    }

    public function getProcessOptionsFailure()
    {
        return $this->processOptionsFailure;
    }

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

    public function getByProcessStatus($status)
    {
        return ReviewDraft::where('process_status', $status)->orderBy('id', 'desc')->get();
    }

    // Reviewers dashboard

    public function getReadyForProcessingBySite($siteId)
    {
        return ReviewDraft::whereNotNull('game_id')
            ->whereNotNull('item_url')
            ->where('site_id', $siteId)
            ->whereNotNull('item_date')
            ->whereNotNull('item_rating')
            ->whereNull('process_status')
            ->get();
    }

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

    public function getByProcessStatusAndSite($status, $siteId)
    {
        return ReviewDraft::where('process_status', $status)->where('site_id', $siteId)->orderBy('id', 'desc')->get();
    }

    public function getByParseStatusAndSite($status, $siteId, $limit = 100)
    {
        $reviewDrafts = ReviewDraft::where('parse_status', $status)->where('process_status', 'Review created')->where('site_id', $siteId)->orderBy('id', 'desc');
        if ($limit) {
            $reviewDrafts = $reviewDrafts->limit($limit);
        }
        return $reviewDrafts->get();
    }

    public function getFailedImportStatsBySite($siteId)
    {
        $statsList = DB::select("
            select process_status, count(*) AS count
            from review_drafts
            where process_status <> 'Review created'
            and site_id = ?
            group by process_status
        ", [$siteId]);

        return $statsList;
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
            FROM review_drafts
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
            FROM review_drafts
            WHERE site_id = ?
            AND process_status = 'Review created'
            GROUP BY parse_status
            ORDER BY parse_status
        ", [$siteId]);

        return $parseStatusStats;
    }
}