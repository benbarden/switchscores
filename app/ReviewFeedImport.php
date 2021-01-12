<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReviewFeedImport extends Model
{
    const METHOD_CRON = 'Cron job';
    const METHOD_STAFF = 'Staff';
    const METHOD_USER = 'User';

    /**
     * @var string
     */
    protected $table = 'review_feed_imports';

    /**
     * @var array
     */
    protected $fillable = [
        'import_method', 'site_id', 'user_id', 'is_test', 'feed_id'
    ];

    public function site()
    {
        return $this->hasOne('App\Partner', 'id', 'site_id');
    }

    public function feed()
    {
        return $this->hasOne('App\PartnerFeedLink', 'id', 'feed_id');
    }

    public function user()
    {
        return $this->hasOne('App\User', 'id', 'user_id');
    }

    public function feedItems()
    {
        return $this->hasMany('App\ReviewFeedItem', 'import_id', 'id');
    }

    public function feedItemsTest()
    {
        return $this->hasMany('App\ReviewFeedItemTest', 'import_id', 'id');
    }

    public function isTest()
    {
        return $this->is_test == 1;
    }
}
