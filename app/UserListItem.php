<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserListItem extends Model
{
    /**
     * @var string
     */
    protected $table = 'user_list_items';

    /**
     * @var bool
     */
    public $timestamps = true;

    /**
     * @var array
     */
    protected $fillable = [
        'list_id', 'game_id'
    ];

    public function listParent()
    {
        return $this->belongsTo('App\UserList', 'list_id', 'id');
    }

    public function game()
    {
        return $this->hasOne('App\Game', 'id', 'game_id');
    }
}
