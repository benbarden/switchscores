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
        'tag_name', 'link_title', 'primary_type_id', 'category_id'
    ];

    public function gameTags()
    {
        return $this->hasMany('App\GameTag', 'tag_id', 'id');
    }

    public function category()
    {
        return $this->hasOne('App\Category', 'id', 'category_id');
    }
}
