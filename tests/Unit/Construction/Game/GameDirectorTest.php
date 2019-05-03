<?php

namespace Tests\Unit;

use App\Game;
use Tests\TestCase;

use App\Construction\Game\GameBuilder;
use App\Construction\Game\GameDirector;

class GameDirectorTest extends TestCase
{
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