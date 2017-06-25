<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GameImage extends Model
{
    /**
     * @var string
     */
    protected $table = 'game_images';

    /**
     * @var bool
     */
    public $timestamps = false;
}
