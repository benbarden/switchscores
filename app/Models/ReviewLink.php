<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class ReviewLink extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    const TYPE_MANUAL = 'Manual';
    const TYPE_IMPORTED = 'Imported';
    const TYPE_PARTNER = 'Partner';

    const STANDARD_RATING_SCALE = 10;

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
        'user_id', 'game_id', 'site_id', 'url', 'rating_original', 'rating_normalised', 'review_date', 'review_type', 'description'
    ];

    public function site()
    {
        return $this->hasOne('App\Models\ReviewSite', 'id', 'site_id');
    }

    public function game()
    {
        return $this->hasOne('App\Models\Game', 'id', 'game_id');
    }

    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }
}
