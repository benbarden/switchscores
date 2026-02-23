<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TagCategory extends Model
{
    const CATEGORY_GAMEPLAY = 2;
    const CATEGORY_CONTENT = 5;
    const CATEGORY_MOOD = 6;
    const CATEGORY_VISUAL_STYLE = 7;
    const CATEGORY_VIEWPOINT = 13;
    const CATEGORY_GAME_TYPE = 14;

    /**
     * @var string
     */
    protected $table = 'tag_categories';

    /**
     * @var array
     */
    protected $fillable = [
        'name', 'link_title', 'category_order', 'taxonomy_reviewed'
    ];

    public function tags()
    {
        return $this->hasMany('App\Models\Tag', 'tag_category_id', 'id')
            ->orderBy('tag_name');
    }
}
