<?php

namespace Tests\Unit\Services\Eshop;

use App\Services\Eshop\LoaderEurope;

use Illuminate\Support\Collection;
use Tests\TestCase;
#use Illuminate\Foundation\Testing\DatabaseMigrations;
#use Illuminate\Foundation\Testing\DatabaseTransactions;

class LoaderEuropeTest extends TestCase
{
    /**
     * @var LoaderEurope
     */
    private $loader;

    public function setUp(): void
    {
        $this->loader = new LoaderEurope();

        parent::setUp();
    }

    public function tearDown(): void
    {
        unset($this->loader);

        parent::tearDown();
    }

    public function testLoadGames()
    {
        $this->loader->loadLocalData('europe-test-25-games.json');
        $responseArray = $this->loader->getResponseData();

        $this->assertArrayHasKey('response', $responseArray);

        $gameData = $responseArray['response']['docs'];
        if (!is_array($gameData)) {
            $this->fail('Cannot load game data');
        }
    }

    public function testGamesArrayLength()
    {
        $this->loader->loadLocalData('europe-test-25-games.json');
        $responseArray = $this->loader->getResponseData();
        $gameData = $responseArray['response']['docs'];

        // Expecting 25 records
        $this->assertEquals(25, count(array_keys($gameData)));

        // And 48 keys
        $this->assertEquals(48, count(array_keys($gameData[0])));
    }

    public function testLoadGames1500()
    {
        $this->loader->loadLocalData('europe-test-1500-games.json');
        $responseArray = $this->loader->getResponseData();

        $this->assertArrayHasKey('response', $responseArray);

        $gameData = $responseArray['response']['docs'];
        if (!is_array($gameData)) {
            $this->fail('Cannot load game data');
        }
    }

}
