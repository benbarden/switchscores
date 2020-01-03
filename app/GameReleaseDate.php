<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use OwenIt\Auditing\Contracts\Auditable;

class GameReleaseDate extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    const REGION_EU = 'eu';
    const REGION_US = 'us';
    const REGION_JP = 'jp';

    /**
     * @var string
     */
    protected $table = 'game_release_dates';

    /**
     * @var bool
     */
    public $timestamps = true;

    /**
     * @var array
     */
    protected $fillable = [
        'game_id', 'region', 'release_date', 'is_released', 'upcoming_date', 'release_year'
    ];

    public function game()
    {
        return $this->hasOne('App\Game', 'id', 'game_id');
    }
}
