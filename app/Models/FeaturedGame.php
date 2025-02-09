<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeaturedGame extends Model
{
    const TYPE_INTERESTING = 1;
    const TYPE_HIDDEN_GEM = 2;
    const TYPE_UNUSUAL_OR_UNIQUE = 3;
    const TYPE_NEEDS_MORE_REVIEWS = 4;

    const STATUS_PENDING = 0;
    const STATUS_ACCEPTED = 1;
    const STATUS_REJECTED = 901;
    const STATUS_ARCHIVED = 999;

    /**
     * @var string
     */
    protected $table = 'featured_games';

    /**
     * @var array
     */
    protected $fillable = [
        'game_id', 'user_id', 'featured_date', 'featured_type', 'status'
    ];

    public function game()
    {
        return $this->hasOne('App\Models\Game', 'id', 'game_id');
    }

    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

    public function getTypeDesc()
    {
        $desc = '';
        switch ($this->featured_type) {
            case self::TYPE_INTERESTING:
                $desc = 'Interesting';
                break;
            case self::TYPE_HIDDEN_GEM;
                $desc = 'Hidden gem';
                break;
            case self::TYPE_UNUSUAL_OR_UNIQUE;
                $desc = 'Unusual or unique';
                break;
            case self::TYPE_NEEDS_MORE_REVIEWS;
                $desc = 'Needs more reviews';
                break;
        }

        return $desc;
    }

    public function getTypeForMemberPage()
    {
        $desc = '';
        switch ($this->featured_type) {
            case self::TYPE_INTERESTING:
                $desc = 'This game looks interesting';
                break;
            case self::TYPE_HIDDEN_GEM;
                $desc = 'This is a hidden gem';
                break;
            case self::TYPE_UNUSUAL_OR_UNIQUE;
                $desc = 'This looks unusual or unique';
                break;
            case self::TYPE_NEEDS_MORE_REVIEWS;
                $desc = 'This needs more reviews';
                break;
        }

        return $desc;
    }

    public function getStatusDesc()
    {
        $desc = '';
        switch ($this->status) {
            case self::STATUS_PENDING:
                $desc = 'Pending';
                break;
            case self::STATUS_ACCEPTED:
                $desc = 'Accepted';
                break;
            case self::STATUS_REJECTED:
                $desc = 'Rejected';
                break;
            case self::STATUS_ARCHIVED:
                $desc = 'Archived';
                break;
        }

        return $desc;
    }
}
