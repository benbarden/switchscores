<?php

namespace Tests\Unit\Services\Shortcode;

use Tests\TestCase;
use Illuminate\Support\Collection;

use App\Services\Shortcode\GameTable;

class GameTableTest extends TestCase
{
    /**
     * @var GameTable
     */
    private $serviceGameTable;

    public function setUp(): void
    {
        parent::setUp();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->serviceGameTable);
    }

    public function testSimpleTable()
    {
        $seedGames = [];
        $seedGames[] = ['id' => 1, 'title' => 'Zelda BOTW'];
        $seedGames[] = ['id' => 2, 'title' => '1-2-Switch'];
        $seedGames[] = ['id' => 3, 'title' => 'Snipperclips'];
        $seedGames[] = ['id' => 4, 'title' => 'Super Bomberman R'];
        $seedGames[] = ['id' => 5, 'title' => 'Some other game'];
        $seedGames[] = ['id' => 6, 'title' => 'I Am Setsuna'];

        $html = '<p>HELLO</p>[gametable ids="1,2,3"]<p>AND THIS IS A MIDDLE LINE</p>[gametable ids="4,5,6"]<p>BYE BYE</p>';

        $this->serviceGameTable = new GameTable($html, null, $seedGames);

        $expected = '<table><tr><td></td></tr></table>';

        $output = $this->serviceGameTable->parseShortcodes();
        $this->assertIsString($output);
//        $this->assertEquals($expected, $output);
    }
}
