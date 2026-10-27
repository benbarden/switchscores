<?php

namespace Tests\Feature\Console;

use App\Domain\DataSource\NintendoCoUk\GamePriceDecision;
use App\Models\Console;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

/**
 * DSNintendoCoUkApplyPrices --console (#132): Switch 1 can be dry-run before it's switched
 * on, but not applied, because the next UpdateGames would write the payload's price back.
 */
class ApplyPricesConsoleTest extends TestCase
{
    public function testApplyIsRefusedForAConsoleThatIsNotSwitchedOn()
    {
        if (GamePriceDecision::usesApiPrices(Console::ID_SWITCH_1)) {
            $this->markTestSkipped('Switch 1 is switched on.');
        }

        $exitCode = Artisan::call('DSNintendoCoUkApplyPrices', ['--console' => 1, '--apply' => true]);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Dry run only', Artisan::output());
    }

    public function testAnUnknownConsoleIsRefused()
    {
        $this->assertSame(1, Artisan::call('DSNintendoCoUkApplyPrices', ['--console' => 3]));
    }
}
