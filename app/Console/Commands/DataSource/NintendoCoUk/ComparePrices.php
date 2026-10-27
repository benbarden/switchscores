<?php

namespace App\Console\Commands\DataSource\NintendoCoUk;

use App\Domain\DataSource\NintendoCoUk\GamePriceDecision;
use App\Domain\DataSource\NintendoCoUk\NsuidPrice;
use App\Domain\DataSource\NintendoCoUk\PriceApply;
use App\Domain\DataSource\NintendoCoUk\PriceDecision;
use App\Models\Console;
use App\Models\DataSource;
use App\Models\DataSourceParsed;
use App\Models\DataSourcePrice;
use App\Models\DataSourceRaw;
use App\Models\Game;
use App\Models\GameImportRuleEshop;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Compares live game prices with the stored price API data, for sizing a console's price
 * problem before anything applies API prices to it (#132, Switch 1). READ ONLY: never
 * writes a game, and doesn't log to the cron log - it's run by hand.
 *
 * Uses PriceApply::decide() (the 7001 SKU is the game), so the numbers are what applying
 * would do. A record with no 7001 SKU and one 7007 price is a bundle sold on its own (Big
 * Game Bundle 10-in-1, the "Edna & Harvey" Bundle); decide() prices those on Switch 1, and
 * they're counted separately here.
 */
class ComparePrices extends Command
{
    const BUNDLE_UNCHANGED = 'bundle-unchanged';
    const BUNDLE_DIFFERS   = 'bundle-differs';
    const PRICE_DIFFERS    = 'price-differs';
    const SALE_ONLY        = 'sale-only';

    protected $signature = 'DSNintendoCoUkComparePrices
                            {--console=1 : Console id to compare (1 = Switch 1)}
                            {--max-age=72 : Ignore SKU prices fetched more than this many hours ago}
                            {--top=40 : How many of the biggest price differences to list}';

    protected $description = 'Read only: compares live game prices with the stored price API data and reports the differences.';

    public function handle(GamePriceDecision $gamePriceDecision, PriceApply $priceApply)
    {
        $consoleId = (int) $this->option('console');
        $maxAge = (int) $this->option('max-age');
        $top = (int) $this->option('top');
        $now = Carbon::now();

        if (!in_array($consoleId, [Console::ID_SWITCH_1, Console::ID_SWITCH_2], true)) {
            $this->error('Unsupported console id: '.$consoleId);
            return self::FAILURE;
        }

        $parsedList = DataSourceParsed::where('source_id', DataSource::DSID_NINTENDO_CO_UK)
            ->where('console_id', $consoleId)
            ->whereNotNull('game_id')
            ->whereNotNull('link_id')
            ->get(['game_id', 'link_id']);

        $rawJsonByLink = DataSourceRaw::where('source_id', DataSource::DSID_NINTENDO_CO_UK)
            ->where('console_id', $consoleId)
            ->whereIn('link_id', $parsedList->pluck('link_id'))
            ->pluck('source_data_json', 'link_id');

        $prices = DataSourcePrice::where('console_id', $consoleId)->get()->keyBy('nsuid');
        $rules = GameImportRuleEshop::whereIn('game_id', $parsedList->pluck('game_id'))->get()->keyBy('game_id');
        $games = Game::whereIn('id', $parsedList->pluck('game_id'))->get()->keyBy('id');

        $counts = [];
        $rows = [];
        $seen = [];

        foreach ($parsedList as $parsed) {
            if (isset($seen[$parsed->game_id])) continue;
            $seen[$parsed->game_id] = true;

            $game = $games[$parsed->game_id] ?? null;
            if (!$game) continue;

            $skuPrices = $gamePriceDecision->skuPrices($rawJsonByLink[$parsed->link_id] ?? null, $prices);
            $decision = $priceApply->decide($game, $skuPrices, $rules[$game->id] ?? null, $now, $maxAge);

            $outcome = $decision->outcome;
            $newPrice = $decision->newPrice;
            $nsuid = $decision->nsuid;

            // decide() prices Switch 1 standalone bundles from their 7007 SKU; on any other
            // console they come back as no-api-price, so they're picked up here.
            $bundle = null;
            if ($decision->nsuid && str_starts_with($decision->nsuid, NsuidPrice::PREFIX_DELUXE)
                && in_array($outcome, [PriceDecision::CHANGED, PriceDecision::UNCHANGED], true)) {
                $bundle = $decision->nsuid;
            } elseif ($outcome === PriceDecision::NO_API_PRICE && ($sku = $priceApply->standaloneBundle($skuPrices))) {
                $bundle = $sku->nsuid;
                $newPrice = $this->money($sku->regular_price);
                $nsuid = $sku->nsuid;
            }

            if ($bundle) {
                $outcome = $newPrice === $decision->oldPrice ? self::BUNDLE_UNCHANGED : self::BUNDLE_DIFFERS;
            } elseif ($outcome === PriceDecision::CHANGED) {
                $outcome = $decision->oldPrice === $decision->newPrice ? self::SALE_ONLY : self::PRICE_DIFFERS;
            }

            $counts[$outcome] = ($counts[$outcome] ?? 0) + 1;

            if (in_array($outcome, [self::PRICE_DIFFERS, self::BUNDLE_DIFFERS], true)
                || ($outcome === PriceDecision::FROZEN && $decision->oldPrice !== $decision->newPrice)) {
                $rows[] = [
                    'outcome' => $outcome,
                    'id' => $game->id,
                    'title' => mb_strimwidth($game->title, 0, 50, '…'),
                    'site' => $decision->oldPrice,
                    'api' => $newPrice,
                    'nsuid' => $nsuid,
                    'gap' => (float) $newPrice - (float) $decision->oldPrice,
                ];
            }
        }

        usort($rows, fn ($a, $b) => abs($b['gap']) <=> abs($a['gap']));

        $this->table(
            ['Outcome', 'Game', 'Title', 'Site', 'API', 'NSUID', 'API - site'],
            array_map(fn ($r) => [$r['outcome'], $r['id'], $r['title'], $r['site'] ?? '-', $r['api'], $r['nsuid'], sprintf('%+.2f', $r['gap'])], array_slice($rows, 0, $top))
        );

        ksort($counts);
        $this->newLine();
        $this->info('--- Outcomes ('.count($seen).' linked games) ---');
        foreach ($counts as $outcome => $count) {
            $this->info(str_pad($outcome.':', 20).$count);
        }

        $gaps = array_column(array_filter($rows, fn ($r) => $r['outcome'] !== PriceDecision::FROZEN), 'gap');
        if ($gaps) {
            $under = array_filter($gaps, fn ($g) => $g > 0);
            $absGaps = array_map('abs', $gaps);
            sort($absGaps);
            $this->newLine();
            $this->info('--- Price differences (price-differs and bundle-differs) ---');
            $this->info(str_pad('Site cheaper:', 20).count($under).' (the site understates the price)');
            $this->info(str_pad('Site dearer:', 20).(count($gaps) - count($under)));
            $this->info(str_pad('Median gap:', 20).sprintf('%.2f', $absGaps[intdiv(count($absGaps), 2)]));
            $this->info(str_pad('Largest gap:', 20).sprintf('%.2f', end($absGaps)));
        }

        $this->newLine();
        $this->warn('READ ONLY - nothing written.');

        return self::SUCCESS;
    }

    private function money(string|float|null $value): ?string
    {
        // Same as PriceApply::money(), so the two compare like for like
        return is_null($value) || $value === '' ? null : number_format((float) $value, 2, '.', '');
    }
}
