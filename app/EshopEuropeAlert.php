<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EshopEuropeAlert extends Model
{
    const TYPE_ERROR = 1;
    const TYPE_WARNING = 2;
    const TYPE_INFO = 3;

    /**
     * @var string
     */
    protected $table = 'eshop_europe_alerts';

    /**
     * @var bool
     */
    public $timestamps = true;

    /**
     * @var array
     */
    protected $fillable = [
        'game_id', 'type', 'error_message', 'current_data', 'new_data'
    ];

    public function game()
    {
        return $this->hasOne('App\Game', 'id', 'game_id');
    }

}
