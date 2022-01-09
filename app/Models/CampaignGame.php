<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignGame extends Model
{
    /**
     * @var string
     */
    protected $table = 'campaign_games';

    /**
     * @var array
     */
    protected $fillable = [
        'campaign_id', 'game_id'
    ];

    public function game()
    {
        return $this->hasOne('App\Models\Game', 'id', 'game_id');
    }

    public function campaign()
    {
        return $this->hasOne('App\Models\Campaign', 'id', 'campaign_id');
    }
}
