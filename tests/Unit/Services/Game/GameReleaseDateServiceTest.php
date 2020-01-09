<?php

namespace Tests\Unit\Services\Game;

use Tests\TestCase;
use Illuminate\Support\Collection;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Services\GameReleaseDateService;
use App\Game;

class GameReleaseDateServiceTest extends TestCase
{
    public function testReleaseYear2018()
    {
        $releaseDate = '2018-01-01';
        $serviceGame = new GameReleaseDateService();
        $releaseYear = $serviceGame->getReleaseYear($releaseDate);

        $this->assertEquals('2018', $releaseYear);
    }

    public function testReleaseYear2020()
    {
        $releaseDate = '2020-01-03';
        $serviceGame = new GameReleaseDateService();
        $releaseYear = $serviceGame->getReleaseYear($releaseDate);

        $this->assertEquals('2020', $releaseYear);
    }

    public function testReleaseYearNull()
    {
        $releaseDate = null;
        $serviceGame = new GameReleaseDateService();
        $releaseYear = $serviceGame->getReleaseYear($releaseDate);

        $this->assertEquals(null, $releaseYear);
    }
}
