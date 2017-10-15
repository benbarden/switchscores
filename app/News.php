<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    /**
     * @var string
     */
    protected $table = 'news';

    /**
     * @var bool
     */
    public $timestamps = true;

    /**
     * @var array
     */
    protected $fillable = [
        'title', 'category_id', 'url', 'content_html', 'game_id'
    ];

    public function category()
    {
        return $this->hasOne('App\NewsCategory', 'id', 'category_id');
    }

    public function game()
    {
        return $this->hasOne('App\Game', 'id', 'game_id');
    }
}
