<?php

namespace Tests\Feature\Console;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

/**
 * DSNintendoCoUkApplyPrices --console (#132). --apply is also refused for a console not in
 * GamePriceDecision::API_PRICE_CONSOLES, but since Switch 1 was switched on there's no
 * such console to test it with.
 */
class ApplyPricesConsoleTest extends TestCase
{
    public function testAnUnknownConsoleIsRefused()
    {
        $this->assertSame(1, Artisan::call('DSNintendoCoUkApplyPrices', ['--console' => 3]));
    }
}
