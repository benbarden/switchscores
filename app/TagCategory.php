<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TagCategory extends Model
{
    const CATEGORY_GENRES_AND_SUB_GENRES = 1;
    const CATEGORY_GAMEPLAY = 2;
    const CATEGORY_SETTING = 3;
    const CATEGORY_STORY = 4;
    const CATEGORY_CONTENT = 5;
    const CATEGORY_MOOD = 6;
    const CATEGORY_VISUAL_STYLE = 7;
    const CATEGORY_TIME_MECHANIC = 8;
    const CATEGORY_DIFFICULTY = 9;
    const CATEGORY_AUDIENCE = 10;
    const CATEGORY_RETROGAMING_ERA = 11;
    const CATEGORY_SCORING = 12;
    const CATEGORY_VIEWPOINT = 13;

    /**
     * @var string
     */
    protected $table = 'tag_categories';

    /**
     * @var array
     */
    protected $fillable = [
        'name', 'link_title', 'category_order'
    ];

    public function tags()
    {
        return $this->hasMany('App\Tag', 'tag_category_id', 'id')
            ->orderBy('tag_name');
    }
}
