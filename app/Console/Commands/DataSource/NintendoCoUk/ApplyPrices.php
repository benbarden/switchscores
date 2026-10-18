<?php

namespace App\Console\Commands\DataSource\NintendoCoUk;

use App\Domain\DataSource\NintendoCoUk\NsuidExtractor;
use App\Domain\DataSource\NintendoCoUk\PriceApply;
use App\Domain\DataSource\NintendoCoUk\PriceDecision;
use App\Domain\Game\Repository as GameRepository;
use App\Models\Console;
use App\Models\DataSource;
use App\Models\DataSourceParsed;
use App\Models\DataSourcePrice;
use App\Models\DataSourceRaw;
use App\Models\Game;
use App\Models\GameImportRuleEshop;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Sets Switch 2 game prices from the per-SKU prices DSNintendoCoUkFetchPrices stored (#162).
 *
 * DRY RUN BY DEFAULT. It reports what it would change and writes nothing unless
 * --apply is passed. Ben's call (2026-10-18): see real examples of the changes before
 * any live price moves, and don't touch the review flags or the ignore_price freezes
 * yet - frozen games are reported, never written. The cron runs it with --apply from
 * 2026-10-18, after a manual --apply on prod was checked on the staff pages.
 *
 * Order: after DSNintendoCoUkUpdateGames, which still writes the scalar-field price
 * first. This overwrites it for Switch 2 games that have a usable standalone SKU and
 * leaves every other game exactly as UpdateGames left it.
 *
 * The decision itself is in PriceApply; this command only loads, loops and reports.
 */
class ApplyPrices extends Command
{
    protected $signature = 'DSNintendoCoUkApplyPrices
                            {--apply : Write the changes. Without it, nothing is written}
                            {--max-age=36 : Ignore SKU prices fetched more than this many hours ago}';

    protected $description = 'Sets Switch 2 game prices from the stored per-SKU price API data. Dry run unless --apply.';

    public function __construct(private GameRepository $repoGame)
    {
        parent::__construct();
    }

    public function handle(NsuidExtractor $extractor, PriceApply $priceApply)
    {
        $apply = (bool) $this->option('apply');
        $maxAge = (int) $this->option('max-age');
        $now = Carbon::now();
        $consoleId = Console::ID_SWITCH_2;

        $parsedList = DataSourceParsed::where('source_id', DataSource::DSID_NINTENDO_CO_UK)
            ->where('console_id', $consoleId)
            ->whereNotNull('game_id')
            // Orphan rows with no link_id can't be matched to a raw record - see the
            // data_source_parsed note in the Switch Scores context.
            ->whereNotNull('link_id')
            ->get(['game_id', 'link_id']);

        $rawJsonByLink = DataSourceRaw::where('source_id', DataSource::DSID_NINTENDO_CO_UK)
            ->where('console_id', $consoleId)
            ->whereIn('link_id', $parsedList->pluck('link_id'))
            ->pluck('source_data_json', 'link_id');

        $prices = DataSourcePrice::where('console_id', $consoleId)->get()->keyBy('nsuid');
        $rules = GameImportRuleEshop::whereIn('game_id', $parsedList->pluck('game_id'))->get()->keyBy('game_id');
        $flags = DB::table('game_flags')->get()->groupBy('game_id')->map(fn ($rows) => $rows->pluck('flag')->implode(','));

        $counts = [];
        $report = [];
        $seen = [];

        foreach ($parsedList as $parsed) {
            if (isset($seen[$parsed->game_id])) {
                continue;
            }
            $seen[$parsed->game_id] = true;

            $game = Game::find($parsed->game_id);
            if (!$game) {
                continue;
            }

            $skuPrices = [];
            foreach ($extractor->extractFromJson($rawJsonByLink[$parsed->link_id] ?? null) as $nsuid) {
                if (isset($prices[$nsuid])) {
                    $skuPrices[] = $prices[$nsuid];
                }
            }

            $decision = $priceApply->decide($game, $skuPrices, $rules[$game->id] ?? null, $now, $maxAge);
            $counts[$decision->outcome] = ($counts[$decision->outcome] ?? 0) + 1;

            $reportable = in_array($decision->outcome, [PriceDecision::CHANGED, PriceDecision::AMBIGUOUS, PriceDecision::API_ZERO], true)
                || ($decision->outcome === PriceDecision::FROZEN && $decision->differs());

            if ($reportable) {
                $report[] = [
                    $decision->outcome,
                    $game->id,
                    mb_strimwidth($game->title, 0, 50, '…'),
                    $this->pair($decision->oldPrice, $decision->newPrice),
                    $this->pair($this->sale($decision->oldDiscounted, $decision->oldDiscountPc), $this->sale($decision->newDiscounted, $decision->newDiscountPc)),
                    $flags[$game->id] ?? '',
                ];
            }

            if ($apply && $decision->outcome === PriceDecision::CHANGED) {
                $priceApply->apply($game, $decision);
                $game->save();
                $this->repoGame->clearCacheCoreData($game->id);
            }
        }

        // Biggest price moves first: those are the ones worth checking against the store.
        usort($report, fn ($a, $b) => $this->gap($b[3]) <=> $this->gap($a[3]));

        $this->table(['Outcome', 'Game', 'Title', 'Price', 'Sale', 'Flags'], $report);

        ksort($counts);
        $this->newLine();
        $this->info('--- Outcomes ---');
        foreach ($counts as $outcome => $count) {
            $this->info(str_pad($outcome.':', 16).$count);
        }

        $this->newLine();
        if ($apply) {
            $this->info('Applied '.($counts[PriceDecision::CHANGED] ?? 0).' change(s).');
            $logger = Log::channel('cron');
            $logger->info(' *************** '.$this->getName().' *************** ');
            $logger->info('Applied: '.json_encode($counts));
        } else {
            $this->warn('DRY RUN - nothing written. Pass --apply to write the changed rows.');
        }

        return self::SUCCESS;
    }

    private function pair(?string $old, ?string $new): string
    {
        $old = $old ?? '-';
        $new = $new ?? '-';
        return $old === $new ? $old : $old.' -> '.$new;
    }

    private function sale(?string $price, ?string $pc): ?string
    {
        return is_null($price) ? null : $price.' ('.$pc.'%)';
    }

    private function gap(string $pair): float
    {
        $parts = explode(' -> ', $pair);
        return count($parts) === 2 ? abs((float) $parts[1] - (float) $parts[0]) : 0;
    }
}
