<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    /**
     * @var string
     */
    protected $table = 'campaigns';

    /**
     * @var bool
     */
    public $timestamps = true;

    /**
     * @var array
     */
    protected $fillable = [
        'name', 'description', 'progress', 'is_active'
    ];

    public function games()
    {
        return $this->hasMany('App\CampaignGame', 'campaign_id', 'id');
    }
}
