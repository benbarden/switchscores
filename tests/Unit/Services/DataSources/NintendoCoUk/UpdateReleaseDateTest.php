<?php

namespace Tests\Unit\Services\DataSources\NintendoCoUk;

use App\Game;
use App\Models\DataSourceParsed;
use App\Services\DataSources\NintendoCoUk\UpdateGame;
use Tests\TestCase;

class UpdateReleaseDateTest extends TestCase
{
    public function testGameFieldsFutureDate()
    {
        $game = new Game();
        $dsParsedItem = new DataSourceParsed(['release_date_eu' => '2020-01-31']);
        $serviceUpdateGame = new UpdateGame($game, $dsParsedItem);

        $serviceUpdateGame->updateReleaseDate();

        $this->assertEquals($serviceUpdateGame->getGame()->eu_release_date, '2020-01-31');
        $this->assertEquals($serviceUpdateGame->getGame()->release_year, '2020');
        $this->assertEquals($serviceUpdateGame->getGame()->eu_is_released, '1');
        $this->assertNotNull($serviceUpdateGame->getGame()->eu_released_on);
    }
}
