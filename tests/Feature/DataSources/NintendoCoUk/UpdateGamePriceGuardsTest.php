<?php

namespace Tests\Feature\DataSources\NintendoCoUk;

use App\Models\DataSourceParsed;
use App\Models\Game;
use App\Models\GameImportRuleEshop;
use App\Services\DataSources\NintendoCoUk\UpdateGame;
use Tests\TestCase;

/**
 * Cover for the price guards in UpdateGame::updatePrice().
 *
 * The guards that reject an untrustworthy standard price used to `return false`, skipping the
 * discount assignments further down. A game whose API price was missing or zero therefore kept
 * an ended sale's discount forever, even once the parsed record had correctly cleared it.
 * Found on 25 games during the 2026-07-20 stale-discount investigation.
 */
class UpdateGamePriceGuardsTest extends TestCase
{
    private function makeGame(array $attributes = []): Game
    {
        $game = new Game();
        $game->price_eshop = $attributes['price_eshop'] ?? null;
        $game->price_eshop_discounted = $attributes['price_eshop_discounted'] ?? null;
        $game->price_eshop_discount_pc = $attributes['price_eshop_discount_pc'] ?? null;
        return $game;
    }

    private function makeParsed(array $attributes = []): DataSourceParsed
    {
        $parsed = new DataSourceParsed();
        $parsed->price_standard = $attributes['price_standard'] ?? null;
        $parsed->price_discounted = $attributes['price_discounted'] ?? null;
        $parsed->price_discount_pc = $attributes['price_discount_pc'] ?? null;
        return $parsed;
    }

    public function testZeroPriceGuardStillClearsAnEndedDiscount()
    {
        // API returned 0.00 (unreliable), game already has a good price, sale has ended.
        $game = $this->makeGame([
            'price_eshop' => 29.99,
            'price_eshop_discounted' => 17.99,
            'price_eshop_discount_pc' => 40.0,
        ]);
        $parsed = $this->makeParsed(['price_standard' => '0.00']);

        (new UpdateGame($game, $parsed))->updatePrice();

        $this->assertEquals(29.99, $game->price_eshop, 'Good price must not be overwritten by zero');
        $this->assertNull($game->price_eshop_discounted, 'Ended discount should still be cleared');
        $this->assertNull($game->price_eshop_discount_pc);
    }

    public function testNullPriceGuardStillClearsAnEndedDiscount()
    {
        $game = $this->makeGame([
            'price_eshop' => 19.99,
            'price_eshop_discounted' => 9.99,
            'price_eshop_discount_pc' => 50.0,
        ]);
        $parsed = $this->makeParsed(['price_standard' => null]);

        (new UpdateGame($game, $parsed))->updatePrice();

        $this->assertEquals(19.99, $game->price_eshop);
        $this->assertNull($game->price_eshop_discounted);
        $this->assertNull($game->price_eshop_discount_pc);
    }

    public function testRejectedPriceDoesNotApplyItsOwnUntrustedDiscount()
    {
        // A payload we don't trust for price shouldn't have its discount trusted either.
        $game = $this->makeGame(['price_eshop' => 29.99]);
        $parsed = $this->makeParsed([
            'price_standard' => '0.00',
            'price_discounted' => '5.00',
            'price_discount_pc' => 80.0,
        ]);

        (new UpdateGame($game, $parsed))->updatePrice();

        $this->assertEquals(29.99, $game->price_eshop);
        $this->assertNull($game->price_eshop_discounted, 'Untrusted payload must not set a discount');
        $this->assertNull($game->price_eshop_discount_pc);
    }

    public function testUsablePriceAndLiveDiscountAreBothApplied()
    {
        $game = $this->makeGame(['price_eshop' => 44.99]);
        $parsed = $this->makeParsed([
            'price_standard' => '44.99',
            'price_discounted' => '22.49',
            'price_discount_pc' => 50.0,
        ]);

        (new UpdateGame($game, $parsed))->updatePrice();

        $this->assertEquals('44.99', $game->price_eshop);
        $this->assertEquals('22.49', $game->price_eshop_discounted);
        $this->assertEquals(50.0, $game->price_eshop_discount_pc);
    }

    public function testZeroPriceIsAppliedWhenGameHasNoExistingPrice()
    {
        // The guard only protects an existing good price; a genuinely free game still gets 0.
        $game = $this->makeGame(['price_eshop' => null]);
        $parsed = $this->makeParsed(['price_standard' => '0.00']);

        (new UpdateGame($game, $parsed))->updatePrice();

        $this->assertEquals('0.00', $game->price_eshop);
    }

    public function testIgnorePriceRuleSkipsEverything()
    {
        // The ignore rule is a deliberate freeze — it must not clear the discount either.
        $game = $this->makeGame([
            'price_eshop' => 18.89,
            'price_eshop_discounted' => 9.99,
            'price_eshop_discount_pc' => 50.0,
        ]);
        $parsed = $this->makeParsed(['price_standard' => '0.89']);

        $rule = new GameImportRuleEshop();
        $rule->ignore_price = 1;

        (new UpdateGame($game, $parsed, $rule))->updatePrice();

        $this->assertEquals(18.89, $game->price_eshop, 'Manual price must be preserved');
        $this->assertEquals(9.99, $game->price_eshop_discounted);
        $this->assertEquals(50.0, $game->price_eshop_discount_pc);
    }
}
