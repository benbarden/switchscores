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
        'tag_name', 'link_title', 'tag_category_id', 'taxonomy_reviewed', 'layout_version',
        'meta_description', 'intro_description'
    ];

    public function gameTags()
    {
        return $this->hasMany('App\Models\GameTag', 'tag_id', 'id');
    }

    public function category()
    {
        return $this->hasOne('App\Models\TagCategory', 'id', 'tag_category_id');
    }

    public function gamesSwitch1()
    {
        return $this->hasMany('App\Models\Game', 'tag_id', 'id')
            ->where('console_id', Console::ID_SWITCH_1);
    }

    public function gamesSwitch2()
    {
        return $this->hasMany('App\Models\Game', 'tag_id', 'id')
            ->where('console_id', Console::ID_SWITCH_2);
    }
}
