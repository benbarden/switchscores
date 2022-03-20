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
        // We assign the id separately as it doesn't seem to assign in the array
        $seedGames = new Collection();
        $seedGamesItem = new Game(['title' => 'Zelda BOTW', 'link_title' => 'zelda-botw', 'image_header' => 'test1.jpg']);
        $seedGamesItem->id = 1;
        $seedGames->push($seedGamesItem);
        $seedGamesItem = new Game(['title' => '1-2-Switch', 'link_title' => '1-2-switch', 'image_header' => 'test2.jpg']);
        $seedGamesItem->id = 2;
        $seedGames->push($seedGamesItem);
        $seedGamesItem = new Game(['title' => 'Super Bomberman R', 'link_title' => 'super-bomberman-r', 'image_header' => 'test3.jpg']);
        $seedGamesItem->id = 3;
        $seedGames->push($seedGamesItem);
        //dd($seedGamesItem->attributesToArray());

        $html = '<p>HELLO</p>[gametable ids="1,2,3"]<p>AND THIS IS A MIDDLE LINE</p>[gametable ids="4,5,6"]<p>BYE BYE</p>';

        $this->serviceDynamicShortcode = new DynamicShortcode($html);
        $this->serviceDynamicShortcode->setSeedGames($seedGames);

        $expected = '<table><tr><td></td></tr></table>';

        $output = $this->serviceDynamicShortcode->parseShortcodes();
        $this->assertIsString($output);
//        $this->assertEquals($expected, $output);
    }
}
