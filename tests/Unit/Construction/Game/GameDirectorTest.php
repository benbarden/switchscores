<?php

namespace Tests\Unit\Construction\Game;

use App\Construction\Game\GameBuilder;
use App\Construction\Game\GameDirector;
use App\Models\Game;
use Tests\TestCase;

class GameDirectorTest extends TestCase
{
    public function testBuildReleaseYear()
    {
        $releaseDate = '2018-01-01';

        $director = new GameDirector();
        $releaseYear = $director->buildReleaseYear($releaseDate);

        $this->assertEquals('2018', $releaseYear);
    }

    public function testReleaseYear2020()
    {
        $releaseDate = '2020-01-03';

        $director = new GameDirector();
        $releaseYear = $director->buildReleaseYear($releaseDate);

        $this->assertEquals('2020', $releaseYear);
    }

    public function testReleaseYearNull()
    {
        $releaseDate = null;

        $director = new GameDirector();
        $releaseYear = $director->buildReleaseYear($releaseDate);

        $this->assertEquals(null, $releaseYear);
    }

    public function testBasicUsage()
    {
        $title = 'Mario';
        $params = ['title' => $title];

        $director = new GameDirector();
        $builder = new GameBuilder();
        $director->setBuilder($builder);

        $director->buildGame($params);

        $game = $builder->getGame();

        $this->assertEquals($title, $game->title);
    }

    public function testBuildNewGame()
    {
        $title = 'Mario';
        $params = ['title' => $title];

        $director = new GameDirector();
        $builder = new GameBuilder();
        $director->setBuilder($builder);

        $director->buildNewGame($params);

        $builderGame = $builder->getGame();

        $this->assertEquals($title, $builderGame->title);
        $this->assertEquals(0, $builderGame->review_count);
    }

    public function testBuildExistingGame()
    {
        $title = 'Mario';
        $mockGame = new Game();
        $mockGame->title = $title;
        $mockGame->review_count = 3;
        $mockGame->rating_avg = 7.5;
        $mockGame->game_rank = 355;

        $linkTitle = 'mario-test-url';
        $params = ['link_title' => $linkTitle];

        $director = new GameDirector();
        $builder = new GameBuilder();
        $director->setBuilder($builder);

        $director->buildExistingGame($mockGame, $params);

        $builderGame = $builder->getGame();

        $this->assertEquals($title, $builderGame->title);
        $this->assertEquals($linkTitle, $builderGame->link_title);
        $this->assertEquals(3, $builderGame->review_count);
        $this->assertEquals(7.5, $builderGame->rating_avg);
        $this->assertEquals(355, $builderGame->game_rank);
    }
}
