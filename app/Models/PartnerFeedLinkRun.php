<?php

namespace App\Models;

use App\Domain\Feed\FeedRunOutcome;
use Illuminate\Database\Eloquent\Model;

/**
 * One import run of a feed link. See App\Domain\PartnerFeedLink\RunRepository.
 */
class PartnerFeedLinkRun extends Model
{
    protected $table = 'partner_feed_link_runs';

    public $timestamps = false;

    protected $fillable = [
        'feed_link_id', 'run_at', 'was_successful', 'outcome', 'http_status', 'content_type',
        'message', 'imported', 'skipped', 'body_excerpt',
    ];

    protected $casts = [
        'run_at' => 'datetime',
        'was_successful' => 'boolean',
    ];

    public function getOutcomeLabel(): string
    {
        return FeedRunOutcome::label($this->outcome);
    }

    public function getOutcomeBadgeClass(): string
    {
        return FeedRunOutcome::badgeClass($this->outcome);
    }
}
