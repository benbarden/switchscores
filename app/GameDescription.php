<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GameDescription extends Model
{
    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;
    const STATUS_REJECTED = 2;

    /**
     * @var string
     */
    protected $table = 'game_descriptions';

    /**
     * @var bool
     */
    public $timestamps = true;

    /**
     * @var array
     */
    protected $fillable = [
        'game_id', 'user_id', 'description', 'status'
    ];

    public function user()
    {
        return $this->hasOne('App\User', 'id', 'user_id');
    }

    public function getStatusDesc()
    {
        $statusDesc = '';

        switch ($this->status) {
            case self::STATUS_PENDING:
                $statusDesc = 'Pending';
                break;
            case self::STATUS_APPROVED:
                $statusDesc = 'Approved';
                break;
            case self::STATUS_REJECTED:
                $statusDesc = 'Rejected';
        }

        return $statusDesc;
    }
}
