<?php

namespace Tests\Unit\Services\Game;

use Tests\TestCase;
use Illuminate\Support\Collection;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Services\GameTitleHashService;
use App\Game;

class GameTitleHashServiceTest extends TestCase
{
    /**
     * @var GameTitleHashService
     */
    private $serviceGameTitleHash;

    public function setUp(): void
    {
        parent::setUp();
        $this->serviceGameTitleHash = new GameTitleHashService();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->serviceGameTitleHash);
    }

    public function testSuperMarioOdyssey()
    {
        $title = 'Super Mario Odyssey';
        $hash = '63c5bbaa5b0bf74da91d1c40dff0cb02';

        $this->assertEquals($hash, $this->serviceGameTitleHash->generateHash($title));
    }

    public function testBreakforcistBattle()
    {
        $title = '#Breakforcist Battle';
        $hash = '33237ac48f51f66661550227626220f9';

        $this->assertEquals($hash, $this->serviceGameTitleHash->generateHash($title));
    }

    public function testAeternoBlade()
    {
        $title = 'AeternoBlade';
        $hash = '8e3106978679e1539960e0850bfad65a';

        $this->assertEquals($hash, $this->serviceGameTitleHash->generateHash($title));
    }

    public function testAeternoBladeII()
    {
        $title = 'AeternoBlade II';
        $hash = '21e4e0006a4ded1afcffcdfcb8d835c5';

        $this->assertEquals($hash, $this->serviceGameTitleHash->generateHash($title));
    }
}
