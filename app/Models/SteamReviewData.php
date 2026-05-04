<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SteamReviewData extends Model
{
    protected $table = 'steam_review_data';

    protected $fillable = [
        'game_id',
        'steam_id',
        'review_score',
        'review_score_desc',
        'total_positive',
        'total_negative',
        'total_reviews',
        'last_synced_at',
    ];

    protected $casts = [
        'last_synced_at' => 'datetime',
    ];

    public function positivePercent(): ?int
    {
        if (!$this->total_reviews) {
            return null;
        }
        return (int) round(($this->total_positive / $this->total_reviews) * 100);
    }
}
