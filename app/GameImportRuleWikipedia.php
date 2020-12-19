<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GameImportRuleWikipedia extends Model
{
    /**
     * @var string
     */
    protected $table = 'game_import_rules_wikipedia';

    /**
     * @var array
     */
    protected $fillable = [
        'game_id',
        'ignore_developers',
        'ignore_publishers',
        'ignore_europe_dates',
        'ignore_us_dates',
        'ignore_jp_dates',
        'ignore_genres',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'ignore_developers' => 'boolean',
        'ignore_publishers' => 'boolean',
        'ignore_europe_dates' => 'boolean',
        'ignore_us_dates' => 'boolean',
        'ignore_jp_dates' => 'boolean',
        'ignore_genres' => 'boolean',
    ];

    public function game()
    {
        return $this->hasOne('App\Game', 'id', 'game_id');
    }

    public function shouldIgnoreDevelopers()
    {
        return $this->ignore_developers == 1;
    }

    public function shouldIgnorePublishers()
    {
        return $this->ignore_publishers == 1;
    }

    public function shouldIgnoreEuropeDates()
    {
        return $this->ignore_europe_dates == 1;
    }

    public function shouldIgnoreUSDates()
    {
        return $this->ignore_us_dates == 1;
    }

    public function shouldIgnoreJPDates()
    {
        return $this->ignore_jp_dates == 1;
    }

    public function shouldIgnoreGenres()
    {
        return $this->ignore_genres == 1;
    }
}
