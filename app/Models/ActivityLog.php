<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    const EVENT_TYPE_USER_GAME_COLLECTION_ADDED = 'UserGameCollectionAdded';
    const EVENT_TYPE_USER_GAME_COLLECTION_REMOVED = 'UserGameCollectionRemoved';
    const EVENT_TYPE_USER_WISHLIST_ADDED = 'UserWishlistAdded';
    const EVENT_TYPE_USER_WISHLIST_REMOVED = 'UserWishlistRemoved';

    const EVENT_MODEL_USER_WISHLIST = 'App\Models\UserWishlist';

    /**
     * Event types for the activity log's type filter, in the order they're listed.
     */
    public static function eventTypes(): array
    {
        return [
            self::EVENT_TYPE_USER_GAME_COLLECTION_ADDED,
            self::EVENT_TYPE_USER_GAME_COLLECTION_REMOVED,
            self::EVENT_TYPE_USER_WISHLIST_ADDED,
            self::EVENT_TYPE_USER_WISHLIST_REMOVED,
        ];
    }

    /**
     * @var string
     */
    protected $table = 'activity_log';

    /**
     * @var array
     */
    protected $fillable = [
        'event_type', 'user_id', 'event_model', 'event_model_id', 'event_details'
    ];

    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

    public function game()
    {
        return $this->hasOne('App\Models\Game', 'id', 'event_model_id');
    }
}
