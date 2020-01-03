<?php


namespace App\Factories\Tests;

use App\GameReleaseDate;
use Illuminate\Support\Collection;

class GameReleaseDateFactory
{
    public static function makeSimpleCollectionForNoChange()
    {
        $gameReleaseDates = new Collection();

        $gameReleaseDate = new GameReleaseDate();
        $gameReleaseDate->region = 'eu';
        $gameReleaseDate->release_date = '2017-03-03';
        $gameReleaseDate->upcoming_date = '2017-03-03';
        $gameReleaseDate->is_released = '1';

        $gameReleaseDates->push($gameReleaseDate);

        return $gameReleaseDates;
    }

    public static function makeFullCollectionWithChanges()
    {
        $gameReleaseDates = new Collection();

        $gameReleaseDateEU = new GameReleaseDate([
            'region' => 'eu',
            'release_date' => '2018-02-01',
            'upcoming_date' => '2018-02-01',
            'is_released' => '1',
        ]);
        $gameReleaseDateUS = new GameReleaseDate([
            'region' => 'us',
            'release_date' => '2018-03-01',
            'upcoming_date' => '2018-03-01',
            'is_released' => '1',
        ]);
        $gameReleaseDateJP = new GameReleaseDate([
            'region' => 'jp',
            'release_date' => '2018-04-01',
            'upcoming_date' => '2018-04-01',
            'is_released' => '1',
        ]);

        $gameReleaseDates->push($gameReleaseDateEU);
        $gameReleaseDates->push($gameReleaseDateUS);
        $gameReleaseDates->push($gameReleaseDateJP);

        return $gameReleaseDates;
    }
}