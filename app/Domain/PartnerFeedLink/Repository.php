<?php

namespace App\Domain\PartnerFeedLink;

use App\Models\PartnerFeedLink;


class Repository
{
    public function find($id)
    {
        return PartnerFeedLink::find($id);
    }

    public function firstBySite($siteId)
    {
        return PartnerFeedLink::where('site_id', $siteId)->first();
    }

    public function getActive()
    {
        $feedLinks = PartnerFeedLink::join('review_sites', 'partner_feed_links.site_id', '=', 'review_sites.id')
            ->select('partner_feed_links.id as feed_link_id', 'partner_feed_links.*', 'review_sites.*')
            ->where('feed_status', PartnerFeedLink::FEED_STATUS_LIVE)
            ->orderBy('review_sites.name', 'asc')
            ->get();
        return $feedLinks;
    }

    public function getInactive()
    {
        $feedLinks = PartnerFeedLink::join('review_sites', 'partner_feed_links.site_id', '=', 'review_sites.id')
            ->select('partner_feed_links.id as feed_link_id', 'partner_feed_links.*', 'review_sites.*')
            ->where('feed_status', '<>', PartnerFeedLink::FEED_STATUS_LIVE)
            ->orderBy('review_sites.name', 'asc')
            ->get();
        return $feedLinks;
    }
}