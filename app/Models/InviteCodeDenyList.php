<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InviteCodeDenyList extends Model
{
    use HasFactory;

    const TYPE_DOMAIN = 'Domain';

    /**
     * @var string
     */
    protected $table = 'invite_code_deny_list';

    /**
     * @var array
     */
    protected $fillable = [
        'deny_item', 'deny_type'
    ];
}
