<?php

namespace Tests\Unit\Services\Shortcode;

use App\Models\Game;
use App\Services\Shortcode\DynamicShortcode;
use Illuminate\Support\Collection;
use Tests\TestCase;

class GameTableTest extends TestCase
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
        $seedGamesItem = new Game(['id' => 3, 'title' => 'Super Bomberman R', 'image_header' => 'test3.jpg']);
        $seedGames->push($seedGamesItem);

        $html = '<p>HELLO</p>[gametable ids="1,2,3"]<p>AND THIS IS A MIDDLE LINE</p>[gametable ids="4,5,6"]<p>BYE BYE</p>';

        $this->serviceDynamicShortcode = new DynamicShortcode($html);
        $this->serviceDynamicShortcode->setSeedGames($seedGames);

        $expected = '<table><tr><td></td></tr></table>';

        $output = $this->serviceDynamicShortcode->parseShortcodes();
        $this->assertIsString($output);
//        $this->assertEquals($expected, $output);
    }
}
