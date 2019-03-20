<?php

namespace Tests\Unit;

use Tests\TestCase;

use App\Builders\GameBuilder;

class GameBuilderTest extends TestCase
{
    public function testTitle()
    {
        $title = 'Yoshi';

        $gameBuilder = new GameBuilder();
        $game = $gameBuilder->setTitle($title)->build();
        $this->assertEquals($title, $game->title);
    }
}
