<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Console extends Model
{
    use HasFactory;

    const ID_SWITCH_1 = 1;
    const ID_SWITCH_2 = 2;

    const DESC_SWITCH_1 = 'Switch 1';
    const DESC_SWITCH_2 = 'Switch 2';

    /**
     * @var string
     */
    protected $table = 'consoles';

}
