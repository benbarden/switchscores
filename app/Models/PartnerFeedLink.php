<?php

namespace App\Models;

use App\Domain\Feed\FeedRunOutcome;
use Illuminate\Database\Eloquent\Model;

class PartnerFeedLink extends Model
{
    const FEED_STATUS_LIVE = 1;
    const FEED_STATUS_TEST = 2;
    const FEED_STATUS_BROKEN = 8;
    const FEED_STATUS_ARCHIVED = 9;

    const DESC_FEED_STATUS_LIVE = 'Live';
    const DESC_FEED_STATUS_TEST = 'Test';
    const DESC_FEED_STATUS_ARCHIVED = 'Archived';
    const DESC_FEED_STATUS_BROKEN = 'Broken or site down';

    const DATA_TYPE_ARRAY = 1;
    const DATA_TYPE_OBJECT = 2;

    const DESC_DATA_TYPE_ARRAY = 'Array';
    const DESC_DATA_TYPE_OBJECT = 'Object';

    const ITEM_NODE_CHANNEL_ITEM = 1;
    const ITEM_NODE_POST = 2;
    const ITEM_NODE_ITEM = 3;
    const ITEM_NODE_ENTRY = 4;

    const DESC_ITEM_NODE_CHANNEL_ITEM = 'channel > item';
    const DESC_ITEM_NODE_POST = 'post';
    const DESC_ITEM_NODE_ITEM = 'item';
    const DESC_ITEM_NODE_ENTRY_ATOM = 'entry (Atom)';

    /**
     * @var string
     */
    protected $table = 'partner_feed_links';

    /**
     * @var array
     */
    protected $fillable = [
        'feed_status', 'site_id', 'console_id', 'feed_url', 'feed_url_prefix', 'data_type', 'item_node',
        'title_match_rule_pattern', 'title_match_rule_index', 'allow_historic_content', 'use_relay',
        'title_match_rate', 'title_match_rate_at',
        'was_last_run_successful', 'last_run_status', 'last_run_outcome', 'last_run_at', 'last_successful_run_at'
    ];

    /**
     * Longest last_run_status shown in the Feed Links table. New messages are one line; this
     * caps the full-page dumps stored before 2026-10-16 until each feed runs again.
     */
    const LAST_RUN_STATUS_DISPLAY_LENGTH = 160;

    /**
     * @var array
     */
    protected $casts = [
        'last_run_at' => 'datetime',
        'last_successful_run_at' => 'datetime',
    ];

    public function site()
    {
        return $this->hasOne('App\Models\ReviewSite', 'id', 'site_id')->orderBy('name');
    }

    public function console()
    {
        return $this->hasOne('App\Models\Console', 'id', 'console_id');
    }

    public function allowHistoric()
    {
        return $this->allow_historic_content == 1;
    }

    public function isParseAsObjects()
    {
        return $this->data_type == self::DATA_TYPE_OBJECT;
    }

    public function isAtom()
    {
        return $this->item_node == self::ITEM_NODE_ENTRY;
    }

    public function isLive()
    {
        return $this->feed_status == self::FEED_STATUS_LIVE;
    }

    /**
     * Fetch this feed through the egress relay rather than directly - for sites that refuse
     * the server's datacentre IP (#174).
     */
    public function usesRelay(): bool
    {
        return $this->use_relay == 1;
    }

    public function hasRun(): bool
    {
        return $this->last_run_at !== null || $this->last_run_status !== null;
    }

    /**
     * Runs from before last_run_outcome existed only have the success flag to go on.
     */
    private function lastRunOutcomeOrFallback(): string
    {
        if ($this->last_run_outcome) {
            return $this->last_run_outcome;
        }
        return $this->was_last_run_successful ? FeedRunOutcome::OK : FeedRunOutcome::ERROR;
    }

    public function getLastRunOutcomeLabel(): string
    {
        return FeedRunOutcome::label($this->lastRunOutcomeOrFallback());
    }

    public function getLastRunBadgeClass(): string
    {
        return FeedRunOutcome::badgeClass($this->lastRunOutcomeOrFallback());
    }

    public function getLastRunStatusShort(): string
    {
        $status = trim(preg_replace('/\s+/', ' ', (string) $this->last_run_status));
        return mb_strimwidth($status, 0, self::LAST_RUN_STATUS_DISPLAY_LENGTH, '...');
    }

    public function getFeedStatusDesc()
    {
        $desc = '';

        switch ($this->feed_status) {
            case self::FEED_STATUS_LIVE:
                $desc = self::DESC_FEED_STATUS_LIVE;
                break;
            case self::FEED_STATUS_TEST:
                $desc = self::DESC_FEED_STATUS_TEST;
                break;
            case self::FEED_STATUS_ARCHIVED:
                $desc = self::DESC_FEED_STATUS_ARCHIVED;
                break;
            case self::FEED_STATUS_BROKEN:
                $desc = self::DESC_FEED_STATUS_BROKEN;
                break;
        }

        return $desc;
    }

    public function getDataTypeDesc()
    {
        $desc = '';

        switch ($this->data_type) {
            case self::DATA_TYPE_ARRAY:
                $desc = self::DESC_DATA_TYPE_ARRAY;
                break;
            case self::DATA_TYPE_OBJECT:
                $desc = self::DESC_DATA_TYPE_OBJECT;
                break;
        }

        return $desc;
    }

    public function getItemNodeDesc()
    {
        $desc = '';

        switch ($this->item_node) {
            case self::ITEM_NODE_CHANNEL_ITEM:
                $desc = self::DESC_ITEM_NODE_CHANNEL_ITEM;
                break;
            case self::ITEM_NODE_POST:
                $desc = self::DESC_ITEM_NODE_POST;
                break;
            case self::ITEM_NODE_ITEM:
                $desc = self::DESC_ITEM_NODE_ITEM;
                break;
            case self::ITEM_NODE_ENTRY:
                $desc = self::DESC_ITEM_NODE_ENTRY_ATOM;
                break;
        }

        return $desc;
    }
}
