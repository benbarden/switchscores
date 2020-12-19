<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NewsCategory extends Model
{
    const CAT_SITE_UPDATES = 1;
    const CAT_TOP_RATED_NEW_RELEASES = 2;
    const CAT_GAME_UPDATES = 3;
    const CAT_RANK_UPDATES = 4;
    const CAT_UPCOMING_GAMES = 5;

    /**
     * @var string
     */
    protected $table = 'news_categories';

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
