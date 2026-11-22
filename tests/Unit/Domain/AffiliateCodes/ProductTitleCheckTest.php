<?php

namespace Tests\Unit\Domain\AffiliateCodes;

use App\Domain\AffiliateCodes\ProductTitleCheck;
use Tests\TestCase;

/**
 * The checks on an Amazon product title, shared by Find match and the US link check. Titles are
 * real ones from Amazon US.
 */
class ProductTitleCheckTest extends TestCase
{
    private function check(): ProductTitleCheck
    {
        return new ProductTitleCheck();
    }

    public function testSwitch2TitleOnASwitch1GameIsFlagged()
    {
        $this->assertSame('Switch 2', $this->check()->consoleWarning('Overcooked! All You Can Eat - Nintendo Switch 2', false));
    }

    public function testSwitch2WithTrademarkSignIsReadAsSwitch2()
    {
        // Amazon writes "Nintendo Switch™ 2"; this used to read as Switch 1
        $title = 'The Legend of Zelda: Tears of the Kingdom – Nintendo Switch™ 2 Edition';

        $this->assertNull($this->check()->consoleWarning($title, true));
        $this->assertSame('Switch 2', $this->check()->consoleWarning($title, false));
    }

    public function testSwitch1TitleOnASwitch2GameIsFlagged()
    {
        $this->assertSame('Switch 1', $this->check()->consoleWarning('Overcooked! All You Can Eat - Nintendo Switch', true));
    }

    public function testMatchingConsoleIsNotFlagged()
    {
        $this->assertNull($this->check()->consoleWarning('Super Mario Odyssey - Nintendo Switch', false));
        $this->assertNull($this->check()->consoleWarning('Split Fiction - Nintendo Switch 2', true));
    }

    public function testTitleWithoutAConsoleIsNotFlagged()
    {
        $this->assertNull($this->check()->consoleWarning('Super Mario Odyssey - US Version', false));
        $this->assertNull($this->check()->consoleWarning('Super Mario Odyssey - US Version', true));
        $this->assertNull($this->check()->consoleWarning(null, true));
    }

    public function testRegionEditionsAreSpotted()
    {
        $this->assertTrue($this->check()->isRegionEdition('Splatoon 2 (Non-US Version)'));
        $this->assertTrue($this->check()->isRegionEdition('Celeste (Nintendo Switch) UK Import'));
        $this->assertTrue($this->check()->isRegionEdition('Hades II - Nintendo Switch 2 (European Version)'));
        $this->assertTrue($this->check()->isRegionEdition('Some Game (Japanese Version)'));
    }

    public function testUsVersionAndPlainTitlesAreNotRegionEditions()
    {
        $this->assertFalse($this->check()->isRegionEdition('Super Mario Odyssey - US Version'));
        $this->assertFalse($this->check()->isRegionEdition('Mario Kart 8 Deluxe - Nintendo Switch'));
        $this->assertFalse($this->check()->isRegionEdition(null));
    }
}
