<?php

namespace Tests\Unit\Construction\GameReleaseDate;

use App\Game;
use App\GameReleaseDate;
use Tests\TestCase;

use App\Construction\GameReleaseDate\Builder;
use App\Construction\GameReleaseDate\Director;

class DirectorTest extends TestCase
{
    public function testNewRecord()
    {
        $gameId = 509;
        $region = GameReleaseDate::REGION_EU;
        $params = [
            'release_date_eu' => '2019-12-05',
            'upcoming_date_eu' => '2019-XX',
            'is_released_eu' => 'on' // we are expecting a checkbox value
        ];
        $releaseYear = '2019';

        $director = new Director();
        $builder = new Builder();
        $director->setBuilder($builder);
        $director->buildNewReleaseDate($region, $gameId, $params);

        $gameReleaseDate = $builder->getGameReleaseDate();

        $this->assertEquals($region, $gameReleaseDate->region);
        $this->assertEquals($gameId, $gameReleaseDate->game_id);
        $this->assertEquals($params['release_date_eu'], $gameReleaseDate->release_date);
        $this->assertEquals($params['upcoming_date_eu'], $gameReleaseDate->upcoming_date);
        $this->assertEquals('1', $gameReleaseDate->is_released);
        $this->assertEquals($releaseYear, $gameReleaseDate->release_year);
    }

    public function testExistingRecord()
    {
        $gameReleaseDate = new GameReleaseDate;
        $gameReleaseDate->game_id = 1019;
        $gameReleaseDate->region = GameReleaseDate::REGION_US;
        $gameReleaseDate->release_date = '2020-02-10';
        $gameReleaseDate->upcoming_date = '2020-02-10';
        $gameReleaseDate->is_released = '0';
        $gameReleaseDate->release_year = '2020';

        $params = [
            'release_date_us' => '2019-02-10',
            'upcoming_date_us' => '2019-02-10',
            'is_released_us' => 'on'
        ];
        $releaseYear = '2019';

        $director = new Director();
        $builder = new Builder();
        $director->setBuilder($builder);
        $director->buildExistingReleaseDate($gameReleaseDate, $params);

        $gameReleaseDate = $builder->getGameReleaseDate();

        $this->assertEquals(GameReleaseDate::REGION_US, $gameReleaseDate->region);
        $this->assertEquals(1019, $gameReleaseDate->game_id);
        $this->assertEquals('2019-02-10', $gameReleaseDate->release_date);
        $this->assertEquals('2019-02-10', $gameReleaseDate->upcoming_date);
        $this->assertEquals('1', $gameReleaseDate->is_released);
        $this->assertEquals($releaseYear, $gameReleaseDate->release_year);
    }
}
