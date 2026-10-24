<?php

namespace Tests\Unit\Models;

use App\Models\Game;
use Tests\TestCase;

/**
 * Video type labels on the public game page ("Watch trailer" / "Watch gameplay") (#36).
 * Null is "not set yet" and 0 is "no video"; neither shows a label.
 */
class GameVideoTypeTest extends TestCase
{
    private function descFor($videoType): string
    {
        $game = new Game();
        $game->video_type = $videoType;
        return $game->getVideoTypeDesc();
    }

    public function testTrailerAndGameplay()
    {
        $this->assertSame('trailer', $this->descFor(1));
        $this->assertSame('gameplay', $this->descFor(2));
    }

    public function testNoVideoAndNotSetHaveNoLabel()
    {
        $this->assertSame('', $this->descFor(0));
        $this->assertSame('', $this->descFor(null));
    }

    public function testValuesReadFromTheDbAsStrings()
    {
        // MySQL returns tinyint columns as strings unless cast
        $this->assertSame('trailer', $this->descFor('1'));
        $this->assertSame('', $this->descFor(''));
    }
}
