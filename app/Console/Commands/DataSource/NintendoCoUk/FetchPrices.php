<?php

namespace App\Console\Commands\DataSource\NintendoCoUk;

use App\Domain\DataSource\NintendoCoUk\NsuidExtractor;
use App\Domain\DataSource\NintendoCoUk\PriceLookup;
use App\Domain\DataSource\NintendoCoUk\PriceStore;
use App\Domain\JobRun\CronRunContext;
use App\Models\Console;
use App\Models\DataSource;
use App\Models\DataSourceRaw;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Fetches per-SKU prices from the Nintendo eShop price API into data_source_prices.
 *
 * WHY: the search payload only carries scalar price fields (price_regular_f /
 * price_sorting_f). For a Switch 2 Edition with an upgrade pack, price_sorting_f
 * holds the UPGRADE price - "the cheapest purchasable SKU" - so the site published
 * £16.99 for a £66.99 Kirby. Nothing in the payload distinguishes an upgrade pack
 * from a legitimately cheaper base edition. The price API returns prices per NSUID,
 * and the NSUID prefix is the discriminator.
 *
 * A prod sweep on 2026-07-20 found 56 wrong live prices this way, 28 of them
 * carrying no review flag at all. Every error understated the price.
 *
 * WHERE IT RUNS: after the raw import, before ImportParseLink parse. The parser
 * reads what this command stored; it never makes HTTP calls itself.
 *
 * SCOPE: Switch 2 by default; --console=1 for Switch 1, which has the same problem in
 * mirror image. Both run nightly (config/cron-groups.php).
 *
 * The endpoint is unofficial and undocumented, with no SLA. A failure here is never
 * fatal: the parser falls back to the scalar price fields, and the failure counts
 * are reported so a degrading endpoint is visible rather than silent.
 *
 * Full analysis: docs/tasks/switch-2-upgrade-pack-pricing.md
 */
class FetchPrices extends Command
{
    protected $signature = 'DSNintendoCoUkFetchPrices
                            {--console=2 : Console id to fetch prices for (2 = Switch 2)}
                            {--dry-run : Call the API and report, but write nothing}
                            {--include-delisted : Also fetch prices for delisted records}
                            {--limit= : Only this many raw records, in id order (for testing, or a large catalogue in slices)}
                            {--offset=0 : Skip this many raw records first, in id order}';

    protected $description = 'Fetches per-SKU prices from the Nintendo eShop price API into data_source_prices.';

    public function handle(
        NsuidExtractor $extractor,
        PriceLookup $lookup,
        PriceStore $store
    ) {
        $consoleId = (int) $this->option('console');
        $dryRun = $this->option('dry-run');
        $includeDelisted = $this->option('include-delisted');

        if (!in_array($consoleId, [Console::ID_SWITCH_1, Console::ID_SWITCH_2], true)) {
            $this->error('Unsupported console id: '.$consoleId);
            return self::FAILURE;
        }

        $query = DataSourceRaw::where('source_id', DataSource::DSID_NINTENDO_CO_UK)
            ->where('console_id', $consoleId);

        if (!$includeDelisted) {
            // A delisted record is no longer in the feed, so its price will not move
            // again. Skipping them keeps the batch to the live catalogue; pass
            // --include-delisted to refresh them anyway.
            $query->where('is_delisted', 0);
        }

        // A slice of the catalogue: Switch 1 has about 9,000 records, so it's tested
        // narrow first and fetched in slices. Id order keeps consecutive slices from overlapping.
        $total = $query->count();
        $limit = $this->option('limit');
        $offset = (int) $this->option('offset');
        $query->orderBy('id');
        if ($limit !== null) {
            $query->limit((int) $limit);
        }
        if ($offset > 0) {
            $query->offset($offset);
        }

        $records = $query->get(['id', 'link_id', 'source_data_json']);

        if ($limit !== null || $offset > 0) {
            $this->info(sprintf('Slice: records %d to %d of %d', $offset + 1, $offset + $records->count(), $total));
        }

        $nsuids = [];

        foreach ($records as $record) {
            foreach ($extractor->extractFromJson($record->source_data_json) as $nsuid) {
                $nsuids[] = $nsuid;
            }
        }

        $nsuids = array_values(array_unique($nsuids));

        $this->info('Raw records scanned: '.$records->count());
        $this->info('Unique NSUIDs found: '.count($nsuids));

        if (empty($nsuids)) {
            $this->warn('No NSUIDs to look up.');
            return self::SUCCESS;
        }

        $this->info('API calls to make: '.(int) ceil(count($nsuids) / PriceLookup::BATCH_SIZE));
        $this->newLine();

        $result = $lookup->fetch($nsuids);

        $this->info('--- Lookup ---');
        $this->info('Requested:      '.$result->requested);
        $this->info('Resolved:       '.$result->resolved());
        $this->info('Not found:      '.$result->notFound());
        $this->info('No response:    '.$result->missing());
        $this->info('API calls:      '.$result->calls());
        $this->info('Failed batches: '.$result->failedBatches());

        if ($result->hasFailures()) {
            // Loud, but not fatal. The import continues on the scalar price fields.
            $this->warn('Some batches failed - those NSUIDs keep their previously stored price.');
        }

        if ($dryRun) {
            $this->newLine();
            $this->warn('DRY RUN - nothing written.');
            $this->reportSkuBreakdown($result);
            return self::SUCCESS;
        }

        $stored = $store->store($result, $consoleId);

        $this->newLine();
        $this->info('--- Stored ---');
        $this->info('New SKUs:       '.$stored->created());
        $this->info('Price changed:  '.$stored->changed());
        $this->info('Unchanged:      '.$stored->unchanged());

        // Console output goes to /dev/null under cron, so the nightly run needs a line in
        // the cron log to be checkable at all (see #159 for jobs that have none).
        $logger = Log::channel('cron');
        $logger->info(' *************** '.$this->getName().' *************** ');
        $logger->info(sprintf(
            'Console %d: %d NSUIDs, %d resolved, %d not found, %d failed batches; stored %d new, %d changed, %d unchanged',
            $consoleId, $result->requested, $result->resolved(), $result->notFound(), $result->failedBatches(),
            $stored->created(), $stored->changed(), $stored->unchanged()
        ));
        if ($result->hasFailures()) {
            $logger->warning('Some price API batches failed - those NSUIDs keep their previously stored price');
            CronRunContext::warn(sprintf(
                '%d of %d price API batches failed; %d NSUIDs resolved of %d',
                $result->failedBatches(), (int) ceil($result->requested / PriceLookup::BATCH_SIZE),
                $result->resolved(), $result->requested
            ));
        }

        $this->reportSkuBreakdown($result);

        return self::SUCCESS;
    }

    /**
     * Break the resolved SKUs down by prefix.
     *
     * The upgrade-pack count is the interesting one: it is the size of the cohort the
     * scalar price fields were getting wrong, and it is worth watching over time
     * rather than rediscovering by hand.
     */
    private function reportSkuBreakdown($result): void
    {
        $game = $upgrade = $deluxe = $other = 0;

        foreach ($result->all() as $price) {
            if (!$price->isResolved()) {
                continue;
            }

            match (true) {
                $price->isStandaloneGame() => $game++,
                $price->isUpgradePack()    => $upgrade++,
                $price->isDeluxeEdition()  => $deluxe++,
                default                    => $other++,
            };
        }

        $this->newLine();
        $this->info('--- SKU types ---');
        $this->info('Standalone game (7001): '.$game);
        $this->info('Upgrade pack (7005):    '.$upgrade);
        $this->info('Deluxe edition (7007):  '.$deluxe);
        $this->info('Other prefix:           '.$other);
    }
}
