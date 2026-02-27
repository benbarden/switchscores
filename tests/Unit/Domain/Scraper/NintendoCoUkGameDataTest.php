<?php

namespace Tests\Unit\Domain\Scraper;

use App\Domain\Scraper\NintendoCoUkGameData;
use Tests\TestCase;

class NintendoCoUkGameDataTest extends TestCase
{
    /**
     * Build minimal HTML with game info sections.
     */
    private function buildHtml(array $sections): string
    {
        $html = '<html><body><section id="gameDetails"><div class="game_info_container">';

        foreach ($sections as $title => $content) {
            $html .= '<div class="system_info">';
            $html .= '<p class="game_info_title">' . $title . '</p>';
            $html .= '<p class="game_info_text">' . $content . '</p>';
            $html .= '</div>';
        }

        $html .= '</div></section></body></html>';

        return $html;
    }

    /**
     * Build HTML with features as links.
     */
    private function buildHtmlWithFeatures(array $features): string
    {
        $html = '<html><body><section id="gameDetails"><div class="game_info_container">';
        $html .= '<div class="system_info">';
        $html .= '<p class="game_info_title">Features</p>';
        $html .= '<p class="game_info_text features">';

        foreach ($features as $feature) {
            $html .= '<a href="#" title="' . $feature . '">' . $feature . '</a> ';
        }

        $html .= '</p>';
        $html .= '</div>';
        $html .= '</div></section></body></html>';

        return $html;
    }

    // ===========================================
    // getCombinedPlayers() tests
    // ===========================================

    public function testCombinedPlayersSinglePlayerOnly()
    {
        $html = $this->buildHtml([
            'Players' => 'Single System (1)',
        ]);

        $scraper = new NintendoCoUkGameData($html);

        $this->assertEquals('1', $scraper->getCombinedPlayers());
    }

    public function testCombinedPlayersLocalMultiplayer()
    {
        $html = $this->buildHtml([
            'Players' => 'Single System (1-4)',
        ]);

        $scraper = new NintendoCoUkGameData($html);

        $this->assertEquals('1-4', $scraper->getCombinedPlayers());
    }

    public function testCombinedPlayersLocalMultiplayerMinTwo()
    {
        $html = $this->buildHtml([
            'Players' => 'Single System (2-4)',
        ]);

        $scraper = new NintendoCoUkGameData($html);

        $this->assertEquals('2-4', $scraper->getCombinedPlayers());
    }

    public function testCombinedPlayersWithWireless()
    {
        $html = $this->buildHtml([
            'Players' => 'Single System (1), Local Wireless (1-8)',
        ]);

        $scraper = new NintendoCoUkGameData($html);

        $this->assertEquals('1-8', $scraper->getCombinedPlayers());
    }

    public function testCombinedPlayersWithOnline()
    {
        $html = $this->buildHtml([
            'Players' => 'Single System (1), Online (1-8)',
        ]);

        $scraper = new NintendoCoUkGameData($html);

        $this->assertEquals('1-8', $scraper->getCombinedPlayers());
    }

    public function testCombinedPlayersFullMultiplayer()
    {
        // Rocket League example
        $html = $this->buildHtml([
            'Players' => 'Single System (1), Local Wireless (1-8), Online (1-8)',
        ]);

        $scraper = new NintendoCoUkGameData($html);

        $this->assertEquals('1-8', $scraper->getCombinedPlayers());
    }

    public function testCombinedPlayersWirelessOnly()
    {
        $html = $this->buildHtml([
            'Players' => 'Local Wireless (2-4)',
        ]);

        $scraper = new NintendoCoUkGameData($html);

        $this->assertEquals('2-4', $scraper->getCombinedPlayers());
    }

    public function testCombinedPlayersOnlineOnly()
    {
        $html = $this->buildHtml([
            'Players' => 'Online (1-16)',
        ]);

        $scraper = new NintendoCoUkGameData($html);

        $this->assertEquals('1-16', $scraper->getCombinedPlayers());
    }

    public function testCombinedPlayersMixedRanges()
    {
        // Local supports 1-2, but online supports 1-8
        $html = $this->buildHtml([
            'Players' => 'Single System (1-2), Online (1-8)',
        ]);

        $scraper = new NintendoCoUkGameData($html);

        $this->assertEquals('1-8', $scraper->getCombinedPlayers());
    }

    public function testCombinedPlayersNoData()
    {
        $html = $this->buildHtml([
            'Categories' => 'Action, Sports',
        ]);

        $scraper = new NintendoCoUkGameData($html);

        $this->assertNull($scraper->getCombinedPlayers());
    }

    // ===========================================
    // Individual field parsing tests
    // ===========================================

    public function testParsePlayersLocal()
    {
        $html = $this->buildHtml([
            'Players' => 'Single System (2-4)',
        ]);

        $scraper = new NintendoCoUkGameData($html);

        $this->assertEquals('2-4', $scraper->getPlayersLocal());
        $this->assertNull($scraper->getPlayersWireless());
        $this->assertNull($scraper->getPlayersOnline());
    }

    public function testParsePlayersWireless()
    {
        $html = $this->buildHtml([
            'Players' => 'Local Wireless (1-8)',
        ]);

        $scraper = new NintendoCoUkGameData($html);

        $this->assertNull($scraper->getPlayersLocal());
        $this->assertEquals('1-8', $scraper->getPlayersWireless());
        $this->assertNull($scraper->getPlayersOnline());
    }

    public function testParsePlayersOnline()
    {
        $html = $this->buildHtml([
            'Players' => 'Online (1-8)',
        ]);

        $scraper = new NintendoCoUkGameData($html);

        $this->assertNull($scraper->getPlayersLocal());
        $this->assertNull($scraper->getPlayersWireless());
        $this->assertEquals('1-8', $scraper->getPlayersOnline());
    }

    public function testParseMultiplayerMode()
    {
        $html = $this->buildHtml([
            'Multiplayer mode' => 'Simultaneous',
        ]);

        $scraper = new NintendoCoUkGameData($html);

        $this->assertEquals('Simultaneous', $scraper->getMultiplayerMode());
    }

    public function testParseFeatures()
    {
        $html = $this->buildHtmlWithFeatures([
            'TV mode',
            'Tabletop mode',
            'Handheld mode',
            'Local multiplayer',
        ]);

        $scraper = new NintendoCoUkGameData($html);
        $features = $scraper->getFeatures();

        $this->assertContains('TV mode', $features);
        $this->assertContains('Tabletop mode', $features);
        $this->assertContains('Handheld mode', $features);
        $this->assertContains('Local multiplayer', $features);
    }

    // ===========================================
    // hasData() tests
    // ===========================================

    public function testHasDataWithPlayers()
    {
        $html = $this->buildHtml([
            'Players' => 'Single System (1)',
        ]);

        $scraper = new NintendoCoUkGameData($html);

        $this->assertTrue($scraper->hasData());
    }

    public function testHasDataWithMultiplayerMode()
    {
        $html = $this->buildHtml([
            'Multiplayer mode' => 'Simultaneous',
        ]);

        $scraper = new NintendoCoUkGameData($html);

        $this->assertTrue($scraper->hasData());
    }

    public function testHasDataWithFeatures()
    {
        $html = $this->buildHtmlWithFeatures(['TV mode']);

        $scraper = new NintendoCoUkGameData($html);

        $this->assertTrue($scraper->hasData());
    }

    public function testHasDataEmpty()
    {
        $html = '<html><body><p>No game info here</p></body></html>';

        $scraper = new NintendoCoUkGameData($html);

        $this->assertFalse($scraper->hasData());
    }

    // ===========================================
    // Edge cases
    // ===========================================

    public function testWhitespaceInPlayerValues()
    {
        $html = $this->buildHtml([
            'Players' => 'Single System ( 1 - 4 )',
        ]);

        $scraper = new NintendoCoUkGameData($html);

        // Should handle whitespace gracefully
        $this->assertEquals('1 - 4', $scraper->getPlayersLocal());
    }

    public function testEmptyHtml()
    {
        $scraper = new NintendoCoUkGameData('');

        $this->assertFalse($scraper->hasData());
        $this->assertNull($scraper->getCombinedPlayers());
    }

    // ===========================================
    // Boolean feature flag tests
    // ===========================================

    public function testHasOnlinePlayFromFeatures()
    {
        $html = $this->buildHtmlWithFeatures(['Online play', 'TV mode']);

        $scraper = new NintendoCoUkGameData($html);

        $this->assertTrue($scraper->hasOnlinePlay());
    }

    public function testHasOnlinePlayFromPlayers()
    {
        $html = $this->buildHtml([
            'Players' => 'Online (1-8)',
        ]);

        $scraper = new NintendoCoUkGameData($html);

        $this->assertTrue($scraper->hasOnlinePlay());
    }

    public function testHasOnlinePlayFalse()
    {
        $html = $this->buildHtml([
            'Players' => 'Single System (1-4)',
        ]);

        $scraper = new NintendoCoUkGameData($html);

        $this->assertFalse($scraper->hasOnlinePlay());
    }

    public function testHasLocalMultiplayerFromFeatures()
    {
        $html = $this->buildHtmlWithFeatures(['Local multiplayer', 'TV mode']);

        $scraper = new NintendoCoUkGameData($html);

        $this->assertTrue($scraper->hasLocalMultiplayer());
    }

    public function testHasLocalMultiplayerFromPlayersLocal()
    {
        $html = $this->buildHtml([
            'Players' => 'Single System (1-4)',
        ]);

        $scraper = new NintendoCoUkGameData($html);

        $this->assertTrue($scraper->hasLocalMultiplayer());
    }

    public function testHasLocalMultiplayerFromWireless()
    {
        $html = $this->buildHtml([
            'Players' => 'Local Wireless (2-4)',
        ]);

        $scraper = new NintendoCoUkGameData($html);

        $this->assertTrue($scraper->hasLocalMultiplayer());
    }

    public function testHasLocalMultiplayerFalseSinglePlayer()
    {
        $html = $this->buildHtml([
            'Players' => 'Single System (1)',
        ]);

        $scraper = new NintendoCoUkGameData($html);

        $this->assertFalse($scraper->hasLocalMultiplayer());
    }

    public function testHasPlayModeTv()
    {
        $html = $this->buildHtmlWithFeatures(['TV mode']);

        $scraper = new NintendoCoUkGameData($html);

        $this->assertTrue($scraper->hasPlayModeTv());
        $this->assertFalse($scraper->hasPlayModeTabletop());
        $this->assertFalse($scraper->hasPlayModeHandheld());
    }

    public function testHasPlayModeTabletop()
    {
        $html = $this->buildHtmlWithFeatures(['Tabletop mode']);

        $scraper = new NintendoCoUkGameData($html);

        $this->assertFalse($scraper->hasPlayModeTv());
        $this->assertTrue($scraper->hasPlayModeTabletop());
        $this->assertFalse($scraper->hasPlayModeHandheld());
    }

    public function testHasPlayModeHandheld()
    {
        $html = $this->buildHtmlWithFeatures(['Handheld mode']);

        $scraper = new NintendoCoUkGameData($html);

        $this->assertFalse($scraper->hasPlayModeTv());
        $this->assertFalse($scraper->hasPlayModeTabletop());
        $this->assertTrue($scraper->hasPlayModeHandheld());
    }

    public function testAllPlayModes()
    {
        $html = $this->buildHtmlWithFeatures(['TV mode', 'Tabletop mode', 'Handheld mode']);

        $scraper = new NintendoCoUkGameData($html);

        $this->assertTrue($scraper->hasPlayModeTv());
        $this->assertTrue($scraper->hasPlayModeTabletop());
        $this->assertTrue($scraper->hasPlayModeHandheld());
    }
}
