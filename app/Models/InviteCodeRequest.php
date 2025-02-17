<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InviteCodeRequest extends Model
{
    /**
     * @var string
     */
    protected $table = 'invite_code_requests';

    /**
     * @var array
     */
    protected $fillable = [
        'waitlist_email', 'waitlist_bio', 'times_requested'
    ];
}
