<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameCrawlLifecycle extends Model
{
    /**
     * @var string
     */
    protected $table = 'game_crawl_lifecycle';

    /**
     * @var array
     */
    protected $fillable = [
        'game_id',
        'status_code',
        'url_crawled',
        'crawled_at',
    ];

    protected $casts = [
        'crawled_at' => 'datetime',
    ];

    public function game()
    {
        return $this->belongsTo(Game::class, 'game_id', 'id');
    }

    /**
     * Check if this is a problem status (non-200).
     */
    public function isProblem(): bool
    {
        return $this->status_code !== 200;
    }

    /**
     * Check if this is a recovery (200 after a problem).
     */
    public function isRecovery(): bool
    {
        return $this->status_code === 200;
    }
}
