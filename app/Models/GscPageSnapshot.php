<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GscPageSnapshot extends Model
{
    use HasFactory;

    protected $table = 'gsc_page_snapshots';

    protected $fillable = [
        'page_url',
        'page_type',
        'game_id',
        'snapshot_date',
        'window_days',
        'clicks',
        'impressions',
        'avg_position',
        'query_count',
        'top_queries',
    ];

    protected $casts = [
        'snapshot_date' => 'date',
        'top_queries'   => 'array',
    ];

    public function game()
    {
        return $this->hasOne('App\Models\Game', 'id', 'game_id');
    }

    /**
     * Scope for a given snapshot date & window
     */
    public function scopeSnapshot($query, string $date, int $windowDays = 28)
    {
        return $query
            ->where('snapshot_date', $date)
            ->where('window_days', $windowDays);
    }

    /**
     * Scope by page type
     */
    public function scopePageType($query, string $type)
    {
        return $query->where('page_type', $type);
    }
}
