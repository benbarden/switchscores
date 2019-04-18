<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GameChangeHistory extends Model
{
    const SOURCE_ESHOP_EUROPE = 'eShop Europe';
    const SOURCE_ESHOP_US = 'eShop US';
    const SOURCE_WIKIPEDIA = 'Wikipedia';
    const SOURCE_ADMIN = 'Admin';
    const SOURCE_MEMBER = 'Member';

    const CHANGE_TYPE_INSERT = 'Insert';
    const CHANGE_TYPE_UPDATE = 'Update';
    const CHANGE_TYPE_DELETE = 'Delete';

    const TABLE_NAME_GAMES = 'games';

    /**
     * @var string
     */
    protected $table = 'game_change_history';

    /**
     * @var bool
     */
    public $timestamps = true;

    /**
     * @var array
     */
    protected $fillable = [
        'game_id', 'affected_table_name', 'source', 'change_type',
        'data_old', 'data_new', 'data_changed', 'user_id',
    ];

    public function game()
    {
        return $this->hasOne('App\Game', 'id', 'game_id');
    }

    public function user()
    {
        return $this->hasOne('App\User', 'id', 'user_id');
    }
}
