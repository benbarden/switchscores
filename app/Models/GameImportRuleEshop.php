<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameImportRuleEshop extends Model
{
    /**
     * @var string
     */
    protected $table = 'game_import_rules_eshop';

    /**
     * @var array
     */
    protected $fillable = [
        'game_id',
        'ignore_publishers',
        'ignore_europe_dates',
        'ignore_price',
        'ignore_players',
        'ignore_genres',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'ignore_publishers' => 'boolean',
        'ignore_europe_dates' => 'boolean',
        'ignore_price' => 'boolean',
        'ignore_players' => 'boolean',
        'ignore_genres' => 'boolean',
    ];

    public function game()
    {
        return $this->hasOne('App\Game', 'id', 'game_id');
    }

    public function shouldIgnorePublishers()
    {
        return $this->ignore_publishers == 1;
    }

    public function shouldIgnoreEuropeDates()
    {
        return $this->ignore_europe_dates == 1;
    }

    public function shouldIgnorePrice()
    {
        return $this->ignore_price == 1;
    }

    public function shouldIgnorePlayers()
    {
        return $this->ignore_players == 1;
    }

    public function shouldIgnoreGenres()
    {
        return $this->ignore_genres == 1;
    }
}
