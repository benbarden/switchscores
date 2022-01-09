<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuickReview extends Model
{
    const STATUS_PENDING = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 9;

    /**
     * @var string
     */
    protected $table = 'quick_reviews';

    /**
     * @var array
     */
    protected $fillable = [
        'user_id', 'game_id', 'site_id', 'review_score', 'review_body', 'item_status'
    ];

    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

    public function game()
    {
        return $this->hasOne('App\Models\Game', 'id', 'game_id');
    }

    public function getStatusDesc()
    {
        $statusDesc = null;

        switch ($this->item_status) {
            case self::STATUS_PENDING:
                $statusDesc = 'Pending';
                break;
            case self::STATUS_ACTIVE:
                $statusDesc = 'Active';
                break;
            case self::STATUS_INACTIVE:
                $statusDesc = 'Inactive';
                break;
            default:
                $statusDesc = 'Unknown';
                break;
        }

        return $statusDesc;
    }
}
