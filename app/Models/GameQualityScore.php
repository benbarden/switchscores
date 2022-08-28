<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameQualityScore extends Model
{
    const MAX_SCORE = 10;

    protected $primaryKey = 'game_id';

    /**
     * @var string
     */
    protected $table = 'game_quality_scores';

    /**
     * @var array
     */
    protected $fillable = [
        'game_id', 'quality_score',
        'has_category', 'has_developers', 'has_publishers', 'has_players', 'has_price',
        'no_conflict_nintendo_eu_release_date',
        'no_conflict_nintendo_price',
        'no_conflict_nintendo_players',
        'no_conflict_nintendo_publishers',
        'no_conflict_nintendo_genre',
    ];

    public function game()
    {
        return $this->hasOne('App\Models\Game', 'id', 'game_id');
    }
}
