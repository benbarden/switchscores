<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    /**
     * @var string
     */
    protected $table = 'tags';

    /**
     * @var array
     */
    protected $fillable = [
        'tag_name', 'link_title', 'tag_category_id'
    ];

    public function gameTags()
    {
        return $this->hasMany('App\Models\GameTag', 'tag_id', 'id');
    }

    public function category()
    {
        return $this->hasOne('App\Models\TagCategory', 'id', 'tag_category_id');
    }
}
