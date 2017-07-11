<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReviewLink extends Model
{
    /**
     * @var string
     */
    protected $table = 'review_links';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var array
     */
    protected $fillable = [
        'game_id', 'site_id', 'url', 'rating_original', 'rating_normalised'
    ];

    public function site()
    {
        return $this->hasOne('App\ReviewSite', 'id', 'site_id');
    }

    public function game()
    {
        return $this->hasOne('App\Game', 'id', 'game_id');
    }
}
