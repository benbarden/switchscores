<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    /**
     * @var string
     */
    protected $table = 'games';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var array
     */
    protected $fillable = [
        'title', 'link_title', 'release_date', 'price_eshop', 'players', 'upcoming', 'upcoming_date',
        'rating_avg', 'review_count', 'overview', 'image_count', 'developer', 'publisher',
        'media_folder', 'amazon_uk_link', 'game_rank', 'video_url', 'release_year',
    ];

    public function charts()
    {
        return $this->hasMany('App\ChartsRanking', 'game_id', 'id');
    }

    public function images()
    {
        return $this->hasMany('App\GameImage', 'game_id', 'id');
    }

    public function reviews()
    {
        return $this->hasMany('App\ReviewLink', 'game_id', 'id');
    }

    public function gameGenres()
    {
        return $this->hasMany('App\GameGenre', 'game_id', 'id');
    }

    // Date helper functions
    public function isRecentlyReleased()
    {
        $releaseDate = $this->release_date;
        if (date('Y-m-d', strtotime('-7 days')) < $releaseDate) {
            return true;
        } else {
            return false;
        }
    }

    public function isUpcoming()
    {
        return $this->upcoming == 1;
    }
}
