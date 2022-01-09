<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameQualityScore extends Model
{
    const MAX_SCORE = 16;

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
        'no_conflict_wikipedia_eu_release_date',
        'no_conflict_wikipedia_us_release_date',
        'no_conflict_wikipedia_jp_release_date',
        'no_conflict_wikipedia_developers',
        'no_conflict_wikipedia_publishers',
        'no_conflict_wikipedia_genre',
    ];

    public function game()
    {
        return $this->hasOne('App\Models\Game', 'id', 'game_id');
    }
}
