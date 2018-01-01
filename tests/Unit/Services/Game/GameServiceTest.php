<?php

namespace Tests\Unit\Services\Review;

use Illuminate\Support\Collection;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Services\GameService;
use App\Game;

class GameServiceTest extends TestCase
{
    public function testReleaseYear2018()
    {
        $releaseDate = '2018-01-01';
        $serviceGame = new GameService();
        $releaseYear = $serviceGame->getReleaseYear($releaseDate);

        $this->assertEquals('2018', $releaseYear);
    }

    public function testReleaseYearNull()
    {
        $releaseDate = null;
        $serviceGame = new GameService();
        $releaseYear = $serviceGame->getReleaseYear($releaseDate);

        $this->assertEquals(null, $releaseYear);
    }
}
