<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FeaturedGame extends Model
{
    const TYPE_INTERESTING = 1;
    const TYPE_HIDDEN_GEM = 2;

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
        return $this->hasOne('App\Game', 'id', 'game_id');
    }

    public function user()
    {
        return $this->hasOne('App\User', 'id', 'user_id');
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
