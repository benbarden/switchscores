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
     * @var array
     */
    protected $fillable = [
        'title', 'category_id', 'url', 'content_html', 'game_id', 'custom_image_url'
    ];

    public function category()
    {
        return $this->hasOne('App\NewsCategory', 'id', 'category_id');
    }

    public function game()
    {
        return $this->hasOne('App\Game', 'id', 'game_id');
    }

    public function isHistoric()
    {
        // If the content is older than 30 days from today, it's history!
        if (date('Y-m-d', strtotime('-30 days')) > $this->created_at) {
            return true;
        } else {
            return false;
        }
    }
}
