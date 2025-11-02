<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeatureQueue extends Model
{
    protected $table = 'feature_queue';
    protected $fillable = ['game_id', 'bucket', 'priority', 'queued_at', 'used_at', 'notes'];
    protected $casts = ['queued_at' => 'datetime', 'used_at' => 'datetime'];

    public function game()
    {
        return $this->belongsTo(Game::class, 'game_id');
    }
}
