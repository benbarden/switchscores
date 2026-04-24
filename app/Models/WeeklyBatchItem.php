<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeeklyBatchItem extends Model
{
    // item_status values
    // Skipped (terminal, not imported):
    const STATUS_ALREADY_IN_DB  = 'already_in_db';
    const STATUS_OUT_OF_RANGE   = 'out_of_range';
    const STATUS_LOW_QUALITY    = 'low_quality';
    const STATUS_BUNDLE         = 'bundle';
    const STATUS_EXCLUDED       = 'excluded';
    // Active (moving through pipeline):
    const STATUS_PENDING            = 'pending';
    const STATUS_FETCH_PENDING      = 'fetch_pending';
    const STATUS_LQ_REVIEW          = 'lq_review';
    const STATUS_PACKSHOT_PENDING   = 'packshot_pending';
    const STATUS_CATEGORY_PENDING   = 'category_pending';
    const STATUS_READY              = 'ready';
    // Done:
    const STATUS_IMPORTED = 'imported';

    const FETCH_STATUS_PENDING  = 'pending';
    const FETCH_STATUS_QUEUED   = 'queued';
    const FETCH_STATUS_FETCHING = 'fetching';
    const FETCH_STATUS_FETCHED  = 'fetched';
    const FETCH_STATUS_FAILED   = 'failed';

    const SKIPPED_STATUSES = [
        self::STATUS_ALREADY_IN_DB,
        self::STATUS_OUT_OF_RANGE,
        self::STATUS_LOW_QUALITY,
        self::STATUS_BUNDLE,
        self::STATUS_EXCLUDED,
    ];

    const ACTIVE_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_FETCH_PENDING,
        self::STATUS_LQ_REVIEW,
        self::STATUS_PACKSHOT_PENDING,
        self::STATUS_CATEGORY_PENDING,
        self::STATUS_READY,
    ];

    const ADVANCED_STATUSES = [
        self::STATUS_IMPORTED,
    ];

    protected $table = 'weekly_batch_items';

    protected $fillable = [
        'batch_id', 'console', 'list_type', 'page_number', 'sort_order',
        'title', 'title_raw', 'release_date', 'price_gbp', 'price_raw',
        'nintendo_genres', 'description',
        'nintendo_url', 'packshot_url',
        'publisher_raw', 'publisher_normalised', 'players',
        'suggested_category', 'suggestion_accepted', 'category', 'collection',
        'item_status',
        'lq_flag', 'lq_flag_reason', 'lq_publisher_name',
        'price_flag', 'price_flag_reason',
        'fetch_status', 'fetch_error',
        'game_id', 'notes',
    ];

    protected $casts = [
        'release_date' => 'date',
        'price_gbp'    => 'decimal:2',
        'lq_flag'      => 'boolean',
        'price_flag'   => 'boolean',
    ];

    public function batch()
    {
        return $this->belongsTo(WeeklyBatch::class, 'batch_id');
    }

    public function game()
    {
        return $this->belongsTo(Game::class, 'game_id');
    }

    public function isSkipped(): bool
    {
        return in_array($this->item_status, self::SKIPPED_STATUSES);
    }

    public function isActive(): bool
    {
        return in_array($this->item_status, self::ACTIVE_STATUSES);
    }

    public function isImported(): bool
    {
        return $this->item_status === self::STATUS_IMPORTED;
    }
}
