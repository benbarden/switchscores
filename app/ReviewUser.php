<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReviewUser extends Model
{
    const STATUS_PENDING = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 9;

    /**
     * @var string
     */
    protected $table = 'review_user';

    /**
     * @var bool
     */
    public $timestamps = true;

    /**
     * @var array
     */
    protected $fillable = [
        'user_id', 'game_id', 'recommend', 'quick_rating', 'review_score', 'review_body', 'item_status'
    ];

    public function user()
    {
        return $this->hasOne('App\User', 'id', 'user_id');
    }

    public function game()
    {
        return $this->hasOne('App\Game', 'id', 'game_id');
    }

    public function quickRating()
    {
        return $this->hasOne('App\ReviewQuickRating', 'id', 'quick_rating');
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
