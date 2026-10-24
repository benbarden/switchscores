<?php

namespace Tests\Feature\Console;

use App\Models\Console;
use App\Models\DataSource;
use App\Models\DataSourceParsed;
use App\Models\DataSourcePrice;
use App\Models\DataSourceRaw;
use App\Models\Game;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

/**
 * DSNintendoCoUkComparePrices (#132): read only, and sorts each Switch 1 game into the
 * right outcome, including bundles sold on their own (a 7007 SKU and no 7001).
 */
class ComparePricesTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * A linked Switch 1 game with a raw record holding these SKUs at these prices.
     */
    private function makeGame(string $sitePrice, array $skus): Game
    {
        $game = new Game();
        $game->title = 'Zqx Compare Prices '.uniqid();
        $game->link_title = 'test-compare-prices-'.uniqid();
        $game->console_id = Console::ID_SWITCH_1;
        $game->price_eshop = $sitePrice;
        $game->save();

        $linkId = random_int(2000000000, 2100000000);
        DataSourceRaw::create([
            'source_id' => DataSource::DSID_NINTENDO_CO_UK,
            'console_id' => Console::ID_SWITCH_1,
            'link_id' => $linkId,
            'title' => $game->title,
            'source_data_json' => json_encode(['nsuid_txt' => array_keys($skus)]),
            'is_delisted' => 0,
        ]);
        DataSourceParsed::create([
            'source_id' => DataSource::DSID_NINTENDO_CO_UK,
            'console_id' => Console::ID_SWITCH_1,
            'game_id' => $game->id,
            'link_id' => $linkId,
            'title' => $game->title,
        ]);
        foreach ($skus as $nsuid => $price) {
            DataSourcePrice::create([
                'nsuid' => $nsuid,
                'console_id' => Console::ID_SWITCH_1,
                'sales_status' => 'onsale',
                'regular_price' => $price,
                'fetched_at' => now(),
            ]);
        }
        return $game;
    }

    public function testReportsEachOutcomeAndWritesNothing()
    {
        $differs = $this->makeGame('7.99', ['70010000980001' => '49.99', '70070000980002' => '69.99']);
        $this->makeGame('19.99', ['70010000980003' => '19.99']);
        $bundle = $this->makeGame('9.99', ['70070000980004' => '24.99']);
        $this->makeGame('14.99', ['70070000980005' => '14.99']);

        $this->assertSame(0, Artisan::call('DSNintendoCoUkComparePrices', ['--console' => 1, '--top' => 1000]));
        $output = Artisan::output();

        $this->assertMatchesRegularExpression('/price-differs\s*\|\s*'.$differs->id.'\s*\|.*7\.99\s*\|\s*49\.99/', $output);
        $this->assertMatchesRegularExpression('/bundle-differs\s*\|\s*'.$bundle->id.'\s*\|.*9\.99\s*\|\s*24\.99/', $output);
        $this->assertMatchesRegularExpression('/bundle-unchanged:\s+\d+/', $output);
        $this->assertMatchesRegularExpression('/unchanged:\s+\d+/', $output);
        $this->assertStringContainsString('READ ONLY', $output);

        $this->assertSame('7.99', $differs->fresh()->price_eshop);
        $this->assertSame('9.99', $bundle->fresh()->price_eshop);
    }
}
