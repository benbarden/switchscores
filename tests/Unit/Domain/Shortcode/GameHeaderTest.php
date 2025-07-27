<?php

namespace Tests\Unit\Domain\Shortcode;

use App\Domain\Game\Repository as GameRepository;

use App\Domain\Shortcode\DynamicShortcode;
use Illuminate\Support\Collection;
use Tests\TestCase;

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
        $repoGame = app(GameRepository::class);

        $seedGames = new Collection();
        $seedGames->push($repoGame->find(1));
        $seedGames->push($repoGame->find(2));

        $html = '<p>HELLO</p>[gameheader ids="1"]<p>AND THIS IS A MIDDLE LINE</p>[gameheader ids="2"]<p>BYE BYE</p>';

        $this->serviceDynamicShortcode = new DynamicShortcode($html);
        $this->serviceDynamicShortcode->setSeedGames($seedGames);

        $expected = '<table><tr><td></td></tr></table>';

        $output = $this->serviceDynamicShortcode->parseShortcodes();
        $this->assertIsString($output);
        //$this->assertEquals($expected, $output);
    }
}
