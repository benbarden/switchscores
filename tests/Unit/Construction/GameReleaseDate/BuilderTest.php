<?php

namespace Tests\Unit\Construction\GameReleaseDate;

use App\GameReleaseDate;
use Tests\TestCase;

use App\Construction\GameReleaseDate\Builder;

class BuilderTest extends TestCase
{
    public function testSetGameId()
    {
        $gameId = 25;

        $builder = new Builder();
        $builder->setGameId($gameId);

        $gameReleaseDate = $builder->getGameReleaseDate();

        $this->assertEquals($gameId, $gameReleaseDate->game_id);
    }

    public function testSetRegion()
    {
        $region = 'eu';

        $builder = new Builder();
        $builder->setRegion($region);

        $gameReleaseDate = $builder->getGameReleaseDate();

        $this->assertEquals($region, $gameReleaseDate->region);
    }

    public function testSetReleaseDate()
    {
        $releaseDate = '2019-01-31';

        $builder = new Builder();
        $builder->setReleaseDate($releaseDate);

        $gameReleaseDate = $builder->getGameReleaseDate();

        $this->assertEquals($releaseDate, $gameReleaseDate->release_date);
    }

    public function testSetIsReleased()
    {
        $isReleased = '1';

        $builder = new Builder();
        $builder->setIsReleased($isReleased);

        $gameReleaseDate = $builder->getGameReleaseDate();

        $this->assertEquals($isReleased, $gameReleaseDate->is_released);
    }

    public function testSetUpcomingDate()
    {
        $upcomingDate = '2019-XX';

        $builder = new Builder();
        $builder->setUpcomingDate($upcomingDate);

        $gameReleaseDate = $builder->getGameReleaseDate();

        $this->assertEquals($upcomingDate, $gameReleaseDate->upcoming_date);
    }

    public function testSetReleaseYear()
    {
        $releaseYear = '2019';

        $builder = new Builder();
        $builder->setReleaseYear($releaseYear);

        $gameReleaseDate = $builder->getGameReleaseDate();

        $this->assertEquals($releaseYear, $gameReleaseDate->release_year);
    }

}
