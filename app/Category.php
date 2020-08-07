<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    /**
     * @var string
     */
    protected $table = 'categories';

    /**
     * @var bool
     */
    public $timestamps = true;

    /**
     * @var array
     */
    protected $fillable = [
        'name', 'link_title', 'parent_id'
    ];

    public function games()
    {
        return $this->hasMany('App\Game', 'category_id', 'id');
    }

    public function tags()
    {
        return $this->hasMany('App\Tag', 'category_id', 'id');
    }

    public function children()
    {
        return $this->hasMany('App\Category', 'parent_id', 'id')->orderBy('name', 'asc');
    }

    public function parent()
    {
        return $this->belongsTo('App\Category', 'parent_id', 'id');
    }
}
