<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactBlocklist extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'contact_blocklist';

    /**
     * @var array
     */
    protected $fillable = [
        'value',
        'type',
        'note',
    ];
}
