<?php

namespace Tests\Unit\Domain\DataSource;

use App\Domain\DataSource\NintendoCoUk\PriceApply;
use App\Domain\DataSource\NintendoCoUk\PriceDecision;
use App\Models\DataSourcePrice;
use App\Models\Game;
use App\Models\GameImportRuleEshop;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * PriceApply picks a game's price from its standalone (7001) SKU.
 *
 * The cases here are the ones from docs/tasks/switch-2-upgrade-pack-pricing.md:
 * an upgrade pack must never become the game's price, a deluxe edition must never
 * become it either, and a freeze is reported but never written. Models are built in
 * memory - decide() makes no writes and no queries.
 */
class PriceApplyTest extends TestCase
{
    private Carbon $now;

    protected function setUp(): void
    {
        parent::setUp();
        $this->now = Carbon::parse('2026-10-18 05:00:00');
    }

    private function game(?string $price, ?string $discounted = null, ?string $pc = null): Game
    {
        $game = new Game();
        $game->price_eshop = $price;
        $game->price_eshop_discounted = $discounted;
        $game->price_eshop_discount_pc = $pc;
        return $game;
    }

    private function sku(string $nsuid, ?string $regular, array $extra = []): DataSourcePrice
    {
        $sku = new DataSourcePrice();
        $sku->nsuid = $nsuid;
        $sku->sales_status = $extra['sales_status'] ?? 'onsale';
        $sku->regular_price = $regular;
        $sku->discount_price = $extra['discount_price'] ?? null;
        $sku->discount_start_at = $extra['discount_start_at'] ?? null;
        $sku->discount_end_at = $extra['discount_end_at'] ?? null;
        $sku->fetched_at = $extra['fetched_at'] ?? $this->now->copy()->subHours(1);
        return $sku;
    }

    private function decide(Game $game, array $skus, ?GameImportRuleEshop $rule = null): PriceDecision
    {
        return (new PriceApply())->decide($game, $skus, $rule, $this->now, 36);
    }

    public function testUpgradePackPriceIsReplacedByTheGamePrice()
    {
        // Kirby S2 (15023): the site showed the 16.99 upgrade pack.
        $decision = $this->decide($this->game('16.99'), [
            $this->sku('70050000000001', '16.99'),
            $this->sku('70010000000001', '66.99'),
        ]);

        $this->assertSame(PriceDecision::CHANGED, $decision->outcome);
        $this->assertSame('66.99', $decision->newPrice);
    }

    public function testDeluxePriceIsNotUsed()
    {
        // Street Fighter 6 (15016): base 34.99, premium edition 49.99. Already right.
        $decision = $this->decide($this->game('34.99'), [
            $this->sku('70070000000001', '49.99'),
            $this->sku('70010000000001', '34.99'),
        ]);

        $this->assertSame(PriceDecision::UNCHANGED, $decision->outcome);
    }

    public function testFrozenGameIsReportedNotChanged()
    {
        $rule = new GameImportRuleEshop();
        $rule->ignore_price = 1;

        $decision = $this->decide($this->game('7.99'), [$this->sku('70010000000001', '58.99')], $rule);

        $this->assertSame(PriceDecision::FROZEN, $decision->outcome);
        $this->assertTrue($decision->differs());

        $game = $this->game('7.99');
        (new PriceApply())->apply($game, $decision);
        $this->assertSame('7.99', $game->price_eshop);
    }

    public function testNoStandaloneSkuIsSkipped()
    {
        $decision = $this->decide($this->game('4.49'), [$this->sku('70050000000001', '4.49')]);

        $this->assertSame(PriceDecision::NO_API_PRICE, $decision->outcome);
    }

    public function testNotFoundAndTerminatedSkusAreNotUsable()
    {
        $decision = $this->decide($this->game('9.99'), [
            $this->sku('70010000000001', null, ['sales_status' => 'not_found']),
            $this->sku('70010000000002', '19.99', ['sales_status' => 'sales_termination']),
        ]);

        $this->assertSame(PriceDecision::NO_API_PRICE, $decision->outcome);
    }

    public function testPreorderPriceIsUsable()
    {
        $decision = $this->decide($this->game(null), [$this->sku('70010000000001', '59.99', ['sales_status' => 'preorder'])]);

        $this->assertSame(PriceDecision::CHANGED, $decision->outcome);
        $this->assertSame('59.99', $decision->newPrice);
    }

    public function testTwoStandaloneSkusAtDifferentPricesAreAmbiguous()
    {
        $decision = $this->decide($this->game('10.00'), [
            $this->sku('70010000000001', '10.00'),
            $this->sku('70010000000002', '20.00'),
        ]);

        $this->assertSame(PriceDecision::AMBIGUOUS, $decision->outcome);
    }

    public function testStalePriceIsNotUsed()
    {
        $decision = $this->decide($this->game('7.99'), [
            $this->sku('70010000000001', '58.99', ['fetched_at' => $this->now->copy()->subHours(37)]),
        ]);

        $this->assertSame(PriceDecision::STALE, $decision->outcome);
    }

    public function testZeroDoesNotReplaceARealPrice()
    {
        $decision = $this->decide($this->game('24.99'), [$this->sku('70010000000001', '0.00')]);

        $this->assertSame(PriceDecision::API_ZERO, $decision->outcome);
    }

    public function testLiveDiscountIsTakenFromTheGameSku()
    {
        // 40% off 24.99 is 14.99; the 4.99 upgrade pack must not leak in.
        $decision = $this->decide($this->game('4.99'), [
            $this->sku('70050000000001', '4.99'),
            $this->sku('70010000000001', '24.99', [
                'discount_price' => '14.99',
                'discount_start_at' => '2026-10-10 00:00:00',
                'discount_end_at' => '2026-10-25 23:59:59',
            ]),
        ]);

        $this->assertSame('24.99', $decision->newPrice);
        $this->assertSame('14.99', $decision->newDiscounted);
        $this->assertSame('40.0', $decision->newDiscountPc);
    }

    public function testEndedDiscountIsCleared()
    {
        $decision = $this->decide($this->game('24.99', '14.99', '40.0'), [
            $this->sku('70010000000001', '24.99', [
                'discount_price' => '14.99',
                'discount_end_at' => '2026-10-17 23:59:59',
            ]),
        ]);

        $this->assertSame(PriceDecision::CHANGED, $decision->outcome);
        $this->assertNull($decision->newDiscounted);
        $this->assertNull($decision->newDiscountPc);
    }

    public function testDiscountPercentMatchesTheSearchPayloadFormat()
    {
        // 9.99 -> 0.99 is 90.09%, which the search payload stores as 90.1.
        $decision = $this->decide($this->game('9.99'), [
            $this->sku('70010000000001', '9.99', ['discount_price' => '0.99']),
        ]);

        $this->assertSame('90.1', $decision->newDiscountPc);
    }

    public function testDecimalFormattingIsNotAChange()
    {
        $decision = $this->decide($this->game('13.5', '9.5', '29.6'), [
            $this->sku('70010000000001', '13.50', ['discount_price' => '9.50']),
        ]);

        $this->assertSame(PriceDecision::UNCHANGED, $decision->outcome);
    }
}
