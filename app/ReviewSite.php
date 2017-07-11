<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReviewSite extends Model
{
    /**
     * @var string
     */
    protected $table = 'review_sites';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var array
     */
    protected $fillable = [
        'name', 'url', 'active', 'rating_scale'
    ];

    public function links()
    {
        return $this->hasMany('App\ReviewLink', 'id', 'site_id');
    }
}
