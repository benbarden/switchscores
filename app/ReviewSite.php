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

    public function links()
    {
        return $this->hasMany('App\ReviewLink', 'id', 'site_id');
    }
}
