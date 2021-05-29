<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InviteCode extends Model
{
    /**
     * @var string
     */
    protected $table = 'invite_codes';

    /**
     * @var array
     */
    protected $fillable = [
        'invite_code', 'times_used', 'times_left', 'is_active'
    ];
}
