<?php

namespace Tests\Unit\Services\DataSources;

use App\Models\Console;
use App\Services\DataSources\NintendoCoUk\Importer;
use Tests\TestCase;

/**
 * Which console a Nintendo.co.uk source record belongs to, from its `system_type`.
 *
 * This used to be an exact string match on two known values, so records where Nintendo
 * repeated "nintendoswitch2" three or four times, or mixed it with a Switch 1 value, were
 * silently stored as Switch 1. That is not cosmetic: the parser picks its Switch 2 price
 * handling off console_id, and the staff "Add game" flow creates the game on it.
 *
 * The values below are the real shapes found in production data on 2026-09-04.
 */
class NintendoCoUkConsoleFromSystemTypeTest extends TestCase
{
    public function testSingleSwitch2Value()
    {
        $this->assertEquals(
            Console::ID_SWITCH_2,
            Importer::consoleIdFromSystemType('nintendoswitch2')
        );
    }

    /** The two forms the old exact-match rule already handled */
    public function testTwoRepeats()
    {
        $this->assertEquals(
            Console::ID_SWITCH_2,
            Importer::consoleIdFromSystemType('nintendoswitch2,nintendoswitch2')
        );
    }

    /** 10 production records looked like this and were all stored as Switch 1 */
    public function testThreeRepeats()
    {
        $this->assertEquals(
            Console::ID_SWITCH_2,
            Importer::consoleIdFromSystemType('nintendoswitch2,nintendoswitch2,nintendoswitch2')
        );
    }

    public function testFourRepeats()
    {
        $this->assertEquals(
            Console::ID_SWITCH_2,
            Importer::consoleIdFromSystemType('nintendoswitch2,nintendoswitch2,nintendoswitch2,nintendoswitch2')
        );
    }

    /** A genuine Switch 2 record carrying a Switch 1 value too - Nintendo's own data error */
    public function testMixedWithASwitch1Value()
    {
        $this->assertEquals(
            Console::ID_SWITCH_2,
            Importer::consoleIdFromSystemType('nintendoswitch_downloadsoftware,nintendoswitch2')
        );
    }

    public function testSwitch2ValueInAnyPosition()
    {
        $this->assertEquals(
            Console::ID_SWITCH_2,
            Importer::consoleIdFromSystemType('nintendoswitch2,nintendoswitch_gamecard')
        );
    }

    /** @dataProvider switch1SystemTypes */
    public function testSwitch1ValuesStaySwitch1(string $systemType)
    {
        $this->assertEquals(
            Console::ID_SWITCH_1,
            Importer::consoleIdFromSystemType($systemType)
        );
    }

    public static function switch1SystemTypes(): array
    {
        return [
            'download software'  => ['nintendoswitch_downloadsoftware'],
            'game card'          => ['nintendoswitch_gamecard'],
            'digital'            => ['nintendoswitch_digitaldistribution'],
            'several repeats'    => ['nintendoswitch_downloadsoftware,nintendoswitch_downloadsoftware,nintendoswitch_gamecard'],
            'mixed switch 1'     => ['nintendoswitch_digitaldistribution,nintendoswitch_gamecard,nintendoswitch_downloadsoftware'],
        ];
    }

    /** Matching is per part, so a value merely containing the string must not count */
    public function testSubstringDoesNotCount()
    {
        $this->assertEquals(
            Console::ID_SWITCH_1,
            Importer::consoleIdFromSystemType('nintendoswitch2extra')
        );
        $this->assertEquals(
            Console::ID_SWITCH_1,
            Importer::consoleIdFromSystemType('notnintendoswitch2')
        );
    }

    public function testEmptyAndNullFallBackToSwitch1()
    {
        $this->assertEquals(Console::ID_SWITCH_1, Importer::consoleIdFromSystemType(''));
        $this->assertEquals(Console::ID_SWITCH_1, Importer::consoleIdFromSystemType(null));
    }

    public function testWhitespaceAroundValuesIsTolerated()
    {
        $this->assertEquals(
            Console::ID_SWITCH_2,
            Importer::consoleIdFromSystemType('nintendoswitch_downloadsoftware, nintendoswitch2')
        );
    }
}
