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

    public function site()
    {
        return $this->hasOne('App\ReviewSite', 'id', 'site_id');
    }

    public function game()
    {
        return $this->hasOne('App\Game', 'game_id', 'id');
    }
}
