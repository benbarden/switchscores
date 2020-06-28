<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DbEditGame extends Model
{
    const DATA_CATEGORY = 'category';

    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;
    const STATUS_DENIED = 2;

    /**
     * @var array
     */
    protected $fillable = [
        'user_id', 'game_id', 'data_to_update', 'current_data', 'new_data', 'status',
        'change_history_id', 'point_transaction_id'
    ];

    /**
     * @var array
     */
    protected $hidden = [
    ];

    /**
     * @var array
     */
    protected $casts = [
    ];

    public function game()
    {
        return $this->hasOne('App\Game', 'id', 'game_id');
    }

    public function user()
    {
        return $this->hasOne('App\User', 'id', 'user_id');
    }

    public function changeHistory()
    {
        return $this->hasOne('App\GameChangeHistory', 'id', 'change_history_id');
    }

    public function pointTransaction()
    {
        return $this->hasOne('App\UserPointTransaction', 'id', 'point_transaction_id');
    }
}
