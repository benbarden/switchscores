<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactSubmission extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'contact_submissions';

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'request_type',
        'message',
        'ip',
        'user_agent',
        'status',
    ];
}
