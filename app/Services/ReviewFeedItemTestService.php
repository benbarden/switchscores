<?php


namespace App\Services;

use App\Models\ReviewFeedItemTest;

class ReviewFeedItemTestService
{
    public function getByImportId($importId)
    {
        return ReviewFeedItemTest::where('import_id', $importId)->orderBy('id', 'desc')->get();
    }

    public function deleteTestItemsBySite($siteId)
    {
        return ReviewFeedItemTest::where('site_id', $siteId)->delete();
    }
}