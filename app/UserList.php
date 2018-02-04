<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserList extends Model
{
    const LIST_TYPE_OWNED = 1;
    const LIST_TYPE_WISHLIST = 2;

    /**
     * @var string
     */
    protected $table = 'user_lists';

    /**
     * @var bool
     */
    public $timestamps = true;

    /**
     * @var array
     */
    protected $fillable = [
        'user_id', 'list_type', 'list_name', 'list_status'
    ];

    public function listItems()
    {
        return $this->hasMany('App\UserListItem', 'list_id', 'id');
    }
}
