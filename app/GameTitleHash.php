<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GameTitleHash extends Model
{
    /**
     * @var string
     */
    protected $table = 'game_title_hashes';

    /**
     * @var bool
     */
    public $timestamps = true;

    /**
     * @var array
     */
    protected $fillable = [
        'title', 'title_hash', 'game_id',
    ];

}
