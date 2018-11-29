<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    /**
     * @var string
     */
    protected $table = 'tags';

    /**
     * @var bool
     */
    public $timestamps = true;

    /**
     * @var array
     */
    protected $fillable = [
        'tag_name', 'link_title'
    ];

    public function gameTags()
    {
        return $this->hasMany('App\GameTag', 'id', 'tag_id');
    }
}
