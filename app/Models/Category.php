<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    const BLURB_NONE = 0;
    const BLURB_A_XX_GAME = 1;
    const BLURB_AN_XX_GAME = 2;
    const BLURB_A_XX = 3;
    const BLURB_AN_XX = 4;
    const BLURB_INVOLVES_XX = 5;

    /**
     * @var string
     */
    protected $table = 'categories';

    /**
     * @var array
     */
    protected $fillable = [
        'name', 'link_title', 'parent_id', 'blurb_option'
    ];

    public function games()
    {
        return $this->hasMany('App\Models\Game', 'category_id', 'id');
    }

    public function gamesSwitch1()
    {
        return $this->hasMany('App\Models\Game', 'category_id', 'id')
            ->where('console_id', Console::ID_SWITCH_1);
    }

    public function gamesSwitch2()
    {
        return $this->hasMany('App\Models\Game', 'category_id', 'id')
            ->where('console_id', Console::ID_SWITCH_2);
    }

    public function children()
    {
        return $this->hasMany('App\Models\Category', 'parent_id', 'id')->orderBy('name', 'asc');
    }

    public function parent()
    {
        return $this->belongsTo('App\Models\Category', 'parent_id', 'id');
    }
}
