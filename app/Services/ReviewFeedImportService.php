<?php


namespace App\Services;

use App\ReviewFeedImport;


class ReviewFeedImportService
{
    public function createCron($siteId = null, $userId = null)
    {
        return $this->create(ReviewFeedImport::METHOD_CRON, $siteId, $userId);
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

    public function getAll()
    {
        return ReviewFeedImport::orderBy('id', 'desc')->get();
    }

    public function getBySiteId($siteId)
    {
        return ReviewFeedImport::where('site_id', $siteId)->orderBy('id', 'desc')->get();
    }
}