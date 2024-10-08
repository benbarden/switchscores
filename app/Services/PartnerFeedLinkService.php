<?php


namespace App\Services;

use App\Models\PartnerFeedLink;

class PartnerFeedLinkService
{
    public function create($values)
    {
        PartnerFeedLink::create($values);
    }

    public function edit(
        PartnerFeedLink $partnerFeedLink, $values
    )
    {
        $values['last_run_status'] = null;
        $partnerFeedLink->fill($values);
        $partnerFeedLink->save();
    }

    public function getAll()
    {
        return PartnerFeedLink::orderBy('id', 'asc')->get();
    }

    public function getActive()
    {
        return PartnerFeedLink::where('feed_status', PartnerFeedLink::FEED_STATUS_LIVE)->orderBy('id', 'asc')->get();
    }

    /**
     * @deprecated
     */
    public function getBySite($siteId)
    {
        return PartnerFeedLink::where('site_id', $siteId)->first();
    }

    /**
     * @param $id
     * @return \App\Models\PartnerFeedLink
     */
    public function find($id)
    {
        return PartnerFeedLink::find($id);
    }

    public function getFeedStatusDropdown()
    {
        $options = [];
        $options[PartnerFeedLink::FEED_STATUS_LIVE] = PartnerFeedLink::DESC_FEED_STATUS_LIVE;
        $options[PartnerFeedLink::FEED_STATUS_TEST] = PartnerFeedLink::DESC_FEED_STATUS_TEST;
        $options[PartnerFeedLink::FEED_STATUS_ARCHIVED] = PartnerFeedLink::DESC_FEED_STATUS_ARCHIVED;
        $options[PartnerFeedLink::FEED_STATUS_BROKEN] = PartnerFeedLink::DESC_FEED_STATUS_BROKEN;
        return $options;
    }

    public function getDataTypeDropdown()
    {
        $options = [];
        $options[PartnerFeedLink::DATA_TYPE_ARRAY] = PartnerFeedLink::DESC_DATA_TYPE_ARRAY;
        $options[PartnerFeedLink::DATA_TYPE_OBJECT] = PartnerFeedLink::DESC_DATA_TYPE_OBJECT;
        return $options;
    }

    public function getItemNodeDropdown()
    {
        $options = [];
        $options[PartnerFeedLink::ITEM_NODE_CHANNEL_ITEM] = PartnerFeedLink::DESC_ITEM_NODE_CHANNEL_ITEM;
        $options[PartnerFeedLink::ITEM_NODE_POST] = PartnerFeedLink::DESC_ITEM_NODE_POST;
        $options[PartnerFeedLink::ITEM_NODE_ITEM] = PartnerFeedLink::DESC_ITEM_NODE_ITEM;
        $options[PartnerFeedLink::ITEM_NODE_ENTRY] = PartnerFeedLink::DESC_ITEM_NODE_ENTRY_ATOM;
        return $options;
    }
}