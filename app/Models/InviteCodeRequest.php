<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InviteCodeRequest extends Model
{
    const STATUS_PENDING = 'Pending';
    const STATUS_INVITE_SENT = 'Invite sent';
    const STATUS_REGISTERED = 'Registered';
    const STATUS_SPAM = 'Spam';
    const STATUS_ARCHIVED = 'Archived';

    /**
     * @var string
     */
    protected $table = 'invite_code_requests';

    /**
     * @var array
     */
    protected $fillable = [
        'waitlist_email', 'waitlist_bio', 'times_requested', 'status'
    ];
}
