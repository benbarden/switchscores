<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReviewLink extends Model
{
    const TYPE_MANUAL = 'Manual';
    const TYPE_IMPORTED = 'Imported';
    const TYPE_PARTNER = 'Partner';

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
        'game_id', 'site_id', 'url', 'rating_original', 'rating_normalised', 'review_date', 'review_type'
    ];

    public function site()
    {
        return $this->hasOne('App\ReviewSite', 'id', 'site_id');
    }

    public function game()
    {
        return $this->hasOne('App\Game', 'id', 'game_id');
    }

    public function user()
    {
        return $this->hasOne('App\User', 'id', 'user_id');
    }
}
