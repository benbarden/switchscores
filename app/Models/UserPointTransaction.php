<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPointTransaction extends Model
{
    // General member features
    const ACTION_TYPE_REGISTER = 100;
    const ACTION_TYPE_BACKLOG_ADD = 110;
    const ACTION_QUICK_REVIEW_ADD = 120;

    // DB edits
    const ACTION_DB_CATEGORY = 210;
    const ACTION_DB_TAG = 220;
    const ACTION_DB_DEVELOPER = 230;
    const ACTION_DB_PUBLISHER = 240;
    const ACTION_DB_VIDEO = 250;
    const ACTION_DB_CORRECTION = 299;

    // Points
    const POINTS_REGISTER = 100;
    const POINTS_BACKLOG_ADD = 10;
    const POINTS_QUICK_REVIEW_ADD = 250;
    const POINTS_DB_EDIT = 20;

    /**
     * @var array
     */
    protected $fillable = [
        'user_id', 'action_type_id', 'action_game_id', 'points_credit', 'points_debit'
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
}
