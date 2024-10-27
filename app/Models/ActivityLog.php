<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    const EVENT_TYPE_USER_GAME_COLLECTION_ADDED = 'UserGameCollectionAdded';
    const EVENT_TYPE_USER_GAME_COLLECTION_REMOVED = 'UserGameCollectionRemoved';

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
}
