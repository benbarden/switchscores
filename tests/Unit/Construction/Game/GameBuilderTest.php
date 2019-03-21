<?php

namespace Tests\Unit;

use Tests\TestCase;

use App\Construction\Game\GameBuilder;

class GameBuilderTest extends TestCase
{
    public function testTitle()
    {
        $title = 'Yoshi';

        $gameBuilder = new GameBuilder();
        $game = $gameBuilder->setTitle($title)->getGame();
        $this->assertEquals($title, $game->title);
    }
}
