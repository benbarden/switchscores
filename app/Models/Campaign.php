<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    /**
     * @var string
     */
    protected $table = 'campaigns';

    /**
     * @var array
     */
    protected $fillable = [
        'name', 'description', 'progress', 'is_active'
    ];

    public function games()
    {
        return $this->hasMany('App\Models\CampaignGame', 'campaign_id', 'id');
    }
}
