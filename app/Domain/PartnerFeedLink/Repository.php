<?php

namespace App\Domain\PartnerFeedLink;

use App\Models\PartnerFeedLink;


class Repository
{
    public function firstBySite($siteId)
    {
        return PartnerFeedLink::where('site_id', $siteId)->first();
    }
}