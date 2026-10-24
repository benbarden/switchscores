<?php

namespace Tests\Feature\Console;

use App\Domain\DataSource\NintendoCoUk\PriceLookup;
use App\Domain\DataSource\NintendoCoUk\PriceLookupResult;
use App\Models\Console;
use App\Models\DataSource;
use App\Models\DataSourceRaw;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

/**
 * DSNintendoCoUkFetchPrices --limit / --offset (#132): a slice of the catalogue, in id order.
 * New records have the highest ids, so an offset of the existing count starts at them.
 */
class FetchPricesSliceTest extends TestCase
{
    use DatabaseTransactions;

    private function makeRaw(string $nsuid): DataSourceRaw
    {
        return DataSourceRaw::create([
            'source_id' => DataSource::DSID_NINTENDO_CO_UK,
            'console_id' => Console::ID_SWITCH_1,
            'link_id' => random_int(2000000000, 2100000000),
            'title' => 'Zqx Fetch Prices Slice',
            'source_data_json' => json_encode(['nsuid_txt' => [$nsuid]]),
            'is_delisted' => 0,
        ]);
    }

    public function testFetchesOnlyTheSlice()
    {
        $existing = DataSourceRaw::where('source_id', DataSource::DSID_NINTENDO_CO_UK)
            ->where('console_id', Console::ID_SWITCH_1)
            ->where('is_delisted', 0)
            ->count();
        $this->makeRaw('70010000990001');
        $this->makeRaw('70010000990002');
        $this->makeRaw('70010000990003');

        $this->mock(PriceLookup::class, function ($mock) {
            $mock->shouldReceive('fetch')->once()
                ->with(['70010000990001', '70010000990002'])
                ->andReturn(new PriceLookupResult(2));
        });

        $exitCode = Artisan::call('DSNintendoCoUkFetchPrices', [
            '--console' => Console::ID_SWITCH_1,
            '--dry-run' => true,
            '--limit' => 2,
            '--offset' => $existing,
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Slice: records '.($existing + 1).' to '.($existing + 2), Artisan::output());
    }
}
