<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Genre extends Model
{
    /**
     * @var string
     */
    protected $table = 'genres';

    /**
     * @var bool
     */
    public $timestamps = false;

    public function gameGenres()
    {
        return $this->hasMany('App\GameGenre', 'genre_id', 'id');
    }
}
