<?php

namespace Tests\Unit\Services\Shortcode;

use Tests\TestCase;
use Illuminate\Support\Collection;

use App\Game;
use App\Services\Shortcode\DynamicShortcode;
use App\Services\GameService;

class GameHeaderTest extends TestCase
{

    /**
     * @var DynamicShortcode
     */
    private $serviceDynamicShortcode;

    public function setUp(): void
    {
        parent::setUp();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->serviceDynamicShortcode);
    }

    public function testSimpleTable()
    {
        $serviceGame = new GameService();

        $seedGames = new Collection();
        $seedGames->push($serviceGame->find(1));
        $seedGames->push($serviceGame->find(2));

        $html = '<p>HELLO</p>[gameheader ids="1"]<p>AND THIS IS A MIDDLE LINE</p>[gameheader ids="2"]<p>BYE BYE</p>';

        $this->serviceDynamicShortcode = new DynamicShortcode($html);
        $this->serviceDynamicShortcode->setSeedGames($seedGames);

        $expected = '<table><tr><td></td></tr></table>';

        $output = $this->serviceDynamicShortcode->parseShortcodes();
        $this->assertIsString($output);
        //$this->assertEquals($expected, $output);
    }
}
