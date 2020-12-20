<?php

namespace Tests\Unit\Services\Shortcode;

use Tests\TestCase;
use Illuminate\Support\Collection;

use App\Game;
use App\Services\Shortcode\DynamicShortcode;

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
        $seedGames = new Collection();
        $seedGamesItem = new Game(['id' => 1, 'title' => 'Zelda BOTW', 'image_header' => 'test1.jpg']);
        $seedGames->push($seedGamesItem);
        $seedGamesItem = new Game(['id' => 2, 'title' => '1-2-Switch', 'image_header' => 'test2.jpg']);
        $seedGames->push($seedGamesItem);

        $html = '<p>HELLO</p>[gameheader ids="1"]<p>AND THIS IS A MIDDLE LINE</p>[gameheader ids="2"]<p>BYE BYE</p>';

        $this->serviceDynamicShortcode = new DynamicShortcode($html);
        $this->serviceDynamicShortcode->setSeedGames($seedGames);

        $expected = '<table><tr><td></td></tr></table>';

        $output = $this->serviceDynamicShortcode->parseShortcodes();
        $this->assertIsString($output);
        //$this->assertEquals($expected, $output);
    }
}
