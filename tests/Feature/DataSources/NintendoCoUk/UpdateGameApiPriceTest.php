<?php

namespace Tests\Feature\DataSources\NintendoCoUk;

use App\Domain\DataSource\NintendoCoUk\PriceDecision;
use App\Models\DataSourceParsed;
use App\Models\Game;
use App\Models\GameImportRuleEshop;
use App\Services\DataSources\NintendoCoUk\UpdateGame;
use Tests\TestCase;

/**
 * Cover for the Switch 2 path in UpdateGame::updatePrice() (#183).
 *
 * When the price API has a usable standalone SKU price, UpdateGames writes that and
 * not the search payload's scalar price, which for an upgrade-pack game is the upgrade's
 * price. Anything the API can't decide falls back to the scalar path, guards included.
 */
class UpdateGameApiPriceTest extends TestCase
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

    public function testChangedDecisionWritesTheApiPriceNotThePayloadPrice()
    {
        // Pokémon Legends: Z-A - the payload carries the upgrade pack's price
        $game = $this->makeGame(['price_eshop' => '7.99']);
        $parsed = $this->makeParsed(['price_standard' => '7.99']);
        $decision = new PriceDecision(
            PriceDecision::CHANGED, '70010000099366',
            '7.99', '58.99',
            null, '47.19',
            null, '20'
        );

        (new UpdateGame($game, $parsed, null, $decision))->updatePrice();

        $this->assertEquals('58.99', $game->price_eshop);
        $this->assertEquals('47.19', $game->price_eshop_discounted);
        $this->assertEquals('20', $game->price_eshop_discount_pc);
    }

    public function testUnchangedDecisionLeavesThePriceAloneEvenWhenThePayloadDiffers()
    {
        $game = $this->makeGame(['price_eshop' => '58.99']);
        $parsed = $this->makeParsed([
            'price_standard' => '7.99',
            'price_discounted' => '3.99',
            'price_discount_pc' => 50.0,
        ]);
        $decision = new PriceDecision(PriceDecision::UNCHANGED, '70010000099366', '58.99', '58.99');

        (new UpdateGame($game, $parsed, null, $decision))->updatePrice();

        $this->assertEquals('58.99', $game->price_eshop);
        $this->assertNull($game->price_eshop_discounted, 'The payload\'s sale must not be applied either');
        $this->assertNull($game->price_eshop_discount_pc);
    }

    public static function fallbackOutcomes(): array
    {
        return [
            'no API price' => [PriceDecision::NO_API_PRICE],
            'stale'        => [PriceDecision::STALE],
            'ambiguous'    => [PriceDecision::AMBIGUOUS],
            'API zero'     => [PriceDecision::API_ZERO],
        ];
    }

    /**
     * @dataProvider fallbackOutcomes
     */
    public function testOutcomesTheApiCannotDecideFallBackToThePayload(string $outcome)
    {
        $game = $this->makeGame(['price_eshop' => '19.99']);
        $parsed = $this->makeParsed([
            'price_standard' => '24.99',
            'price_discounted' => '12.49',
            'price_discount_pc' => 50.0,
        ]);
        $decision = new PriceDecision($outcome, null, '19.99');

        (new UpdateGame($game, $parsed, null, $decision))->updatePrice();

        $this->assertEquals('24.99', $game->price_eshop);
        $this->assertEquals('12.49', $game->price_eshop_discounted);
        $this->assertEquals(50.0, $game->price_eshop_discount_pc);
    }

    public function testFallbackStillHasTheZeroPriceGuard()
    {
        $game = $this->makeGame(['price_eshop' => '29.99']);
        $parsed = $this->makeParsed(['price_standard' => '0.00']);
        $decision = new PriceDecision(PriceDecision::NO_API_PRICE, null, '29.99');

        (new UpdateGame($game, $parsed, null, $decision))->updatePrice();

        $this->assertEquals('29.99', $game->price_eshop);
    }

    public function testIgnorePriceRuleStillWinsOverTheApi()
    {
        $game = $this->makeGame(['price_eshop' => '18.89']);
        $parsed = $this->makeParsed(['price_standard' => '0.89']);
        $rule = new GameImportRuleEshop();
        $rule->ignore_price = 1;
        $decision = new PriceDecision(PriceDecision::CHANGED, '70010000000001', '18.89', '24.99');

        (new UpdateGame($game, $parsed, $rule, $decision))->updatePrice();

        $this->assertEquals('18.89', $game->price_eshop, 'A frozen price must not be written');
    }
}
