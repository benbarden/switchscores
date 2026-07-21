<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataSourcePrice extends Model
{
    protected $table = 'data_source_prices';

    public $timestamps = false;

    protected $fillable = [
        'nsuid', 'console_id', 'sales_status',
        'regular_price', 'discount_price',
        'discount_start_at', 'discount_end_at',
        'first_seen_at', 'fetched_at', 'price_changed_at',
    ];

    protected $casts = [
        'discount_start_at' => 'datetime',
        'discount_end_at'   => 'datetime',
        'first_seen_at'     => 'datetime',
        'fetched_at'        => 'datetime',
        'price_changed_at'  => 'datetime',
    ];

    public function history()
    {
        return $this->hasMany(DataSourcePriceHistory::class, 'nsuid', 'nsuid');
    }

    /**
     * Is there a discount running right now?
     *
     * Checks the END DATE rather than trusting the presence of a discount, which is
     * the whole reason this integration is worth building: an ended sale can be
     * identified from the data instead of waiting for the API to stop sending it.
     * That is what makes the stale-discount bug structurally impossible rather than
     * merely fixed.
     */
    public function hasLiveDiscount(): bool
    {
        if (is_null($this->discount_price)) {
            return false;
        }

        if ($this->discount_end_at && $this->discount_end_at->isPast()) {
            return false;
        }

        return true;
    }
}
