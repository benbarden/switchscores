<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * The same game on different consoles (#44).
 */
class GameGroup extends Model
{
    const MATCH_IDENTICAL = 'identical';
    const MATCH_ENHANCED = 'enhanced';

    protected $table = 'game_groups';

    protected $fillable = ['name', 'match_type', 'note'];

    public function games()
    {
        return $this->hasMany('App\Models\Game', 'game_group_id', 'id');
    }
}
