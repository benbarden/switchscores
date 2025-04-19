<?php

namespace Tests\Unit\Domain\Game;

use App\Domain\GameTitleHash\Repository as GameTitleHashRepository;
use App\Domain\GameTitleHash\HashGenerator;

use Tests\TestCase;

class GameTitleHashTest extends TestCase
{
    /**
     * @var GameTitleHashRepository
     */
    private $repoGameTitleHash;

    /**
     * @var HashGenerator
     */
    private $hashGenerator;

    public function setUp(): void
    {
        parent::setUp();
        $this->repoGameTitleHash = new GameTitleHashRepository();
        $this->hashGenerator = new HashGenerator();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->repoGameTitleHash);
        unset($this->hashGenerator);
    }

    public function testSuperMarioOdyssey()
    {
        $title = 'Super Mario Odyssey';
        $hash = '63c5bbaa5b0bf74da91d1c40dff0cb02';

        $this->assertEquals($hash, $this->hashGenerator->generateHash($title));
    }

    public function testBreakforcistBattle()
    {
        $title = '#Breakforcist Battle';
        $hash = '33237ac48f51f66661550227626220f9';

        $this->assertEquals($hash, $this->hashGenerator->generateHash($title));
    }

    public function testAeternoBlade()
    {
        $title = 'AeternoBlade';
        $hash = '8e3106978679e1539960e0850bfad65a';

        $this->assertEquals($hash, $this->hashGenerator->generateHash($title));
    }

    public function testAeternoBladeII()
    {
        $title = 'AeternoBlade II';
        $hash = '21e4e0006a4ded1afcffcdfcb8d835c5';

        $this->assertEquals($hash, $this->hashGenerator->generateHash($title));
    }
}
