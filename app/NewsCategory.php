<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NewsCategory extends Model
{
    /**
     * @var string
     */
    protected $table = 'news_categories';

    /**
     * @var bool
     */
    public $timestamps = true;

    /**
     * @var array
     */
    protected $fillable = [
        'name', 'link_name'
    ];

    public function news()
    {
        return $this->hasMany('App\News', 'id', 'category_id');
    }
}
