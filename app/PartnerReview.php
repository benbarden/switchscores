<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PartnerReview extends Model
{
    const STATUS_PENDING = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 9;

    /**
     * @var string
     */
    protected $table = 'partner_reviews';

    /**
     * @var bool
     */
    public $timestamps = true;

    /**
     * @var array
     */
    protected $fillable = [
        'user_id', 'site_id', 'game_id', 'item_url', 'item_date', 'item_rating', 'item_status', 'review_link_id'
    ];

    public function user()
    {
        return $this->hasOne('App\User', 'id', 'user_id');
    }

    public function site()
    {
        return $this->hasOne('App\Partner', 'id', 'site_id');
    }

    public function game()
    {
        return $this->hasOne('App\Game', 'id', 'game_id');
    }

    public function reviewLink()
    {
        return $this->hasOne('App\ReviewLink', 'id', 'review_link_id');
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
