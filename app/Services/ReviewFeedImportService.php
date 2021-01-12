<?php


namespace App\Services;

use App\ReviewFeedImport;


class ReviewFeedImportService
{
    public function createCron($siteId = null, $userId = null)
    {
        return $this->create(ReviewFeedImport::METHOD_CRON, $siteId, $userId);
    }

    public function createSiteCron($feedId, $siteId, $isTest = false)
    {
        $isTestValue = $isTest == true ? 1 : 0;

        return ReviewFeedImport::create([
            'import_method' => ReviewFeedImport::METHOD_CRON,
            'site_id' => $siteId,
            'is_test' => $isTestValue,
            'feed_id' => $feedId,
        ]);
    }

    public function create(
        $method, $siteId = null, $userId = null
    )
    {
        return ReviewFeedImport::create([
            'import_method' => $method,
            'site_id' => $siteId,
            'user_id' => $userId
        ]);
    }

    public function getLive($limit = null)
    {
        if ($limit) {
            return ReviewFeedImport::where('is_test', 0)->orderBy('id', 'desc')->limit($limit)->get();
        } else {
            return ReviewFeedImport::where('is_test', 0)->orderBy('id', 'desc')->get();
        }
    }

    public function getAll($limit = null)
    {
        if ($limit) {
            return ReviewFeedImport::orderBy('id', 'desc')->limit($limit)->get();
        } else {
            return ReviewFeedImport::orderBy('id', 'desc')->get();
        }
    }

    public function getBySiteId($siteId)
    {
        return ReviewFeedImport::where('site_id', $siteId)->orderBy('id', 'desc')->get();
    }

    public function getLatestByFeedId($feedId)
    {
        return ReviewFeedImport::where('feed_id', $feedId)->orderBy('id', 'desc')->first();
    }
}