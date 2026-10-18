<?php

namespace App\Console\Commands\DataSource\NintendoCoUk;

use App\Domain\DataSource\NintendoCoUk\GamePriceDecision;
use App\Domain\DataSource\NintendoCoUk\PriceDecision;
use App\Models\Console;
use App\Models\Game;
use App\Models\GameFlag;
use App\Models\GameImportRuleEshop;
use Illuminate\Console\Command;

/**
 * Removes the manual price holds the price API has made unnecessary (#162).
 *
 * For Switch 2 games only. Where the price API has a usable standalone SKU price -
 * decided as if the game had no freeze - this clears the game's ignore_price freeze
 * and its price review flags. Where it hasn't (no 7001 SKU, stale, ambiguous, zero),
 * both stay: the freeze is still the only thing holding that price. Switch 1 is not
 * touched; its prices still come from the search payload.
 *
 * DRY RUN BY DEFAULT, like DSNintendoCoUkApplyPrices. Meant to be run by hand on prod
 * after #183 is live, with a DB dump first. Prices are not written here: once a freeze
 * is gone, the next DSNintendoCoUkUpdateGames sets the API price. The report shows
 * which prices that will move.
 */
class RetirePriceHolds extends Command
{
    /**
     * The price review flags from the June-July 2026 Switch 2 price checks.
     */
    const PRICE_FLAGS = [
        'price-check-deluxe',
        'price-self-heal',
        'price-recheck',
        'price-upgrade-pack',
        'price-sale-monitor',
    ];

    const API_PRICE_OUTCOMES = [PriceDecision::CHANGED, PriceDecision::UNCHANGED];

    protected $signature = 'DSNintendoCoUkRetirePriceHolds
                            {--apply : Remove the freezes and flags. Without it, nothing is written}';

    protected $description = 'Clears ignore_price freezes and price flags on Switch 2 games the price API now prices. Dry run unless --apply.';

    public function handle(GamePriceDecision $gamePriceDecision)
    {
        $apply = (bool) $this->option('apply');

        $frozenIds = GameImportRuleEshop::where('ignore_price', 1)->pluck('game_id')->all();
        $flaggedIds = GameFlag::whereIn('flag', self::PRICE_FLAGS)->pluck('game_id')->all();
        $gameIds = array_values(array_unique(array_merge($frozenIds, $flaggedIds)));

        $games = Game::whereIn('id', $gameIds)
            ->where('console_id', Console::ID_SWITCH_2)
            ->orderBy('id')
            ->get();

        $report = [];
        $counts = ['freezes removed' => 0, 'flags removed' => 0, 'games kept' => 0];

        foreach ($games as $game) {
            $decision = $gamePriceDecision->forGame($game, null, true);
            $retire = $decision && in_array($decision->outcome, self::API_PRICE_OUTCOMES, true);

            $isFrozen = in_array($game->id, $frozenIds);
            $flags = GameFlag::where('game_id', $game->id)->whereIn('flag', self::PRICE_FLAGS)->get();

            $priceMoves = $retire && $decision->differs()
                ? $this->pair($decision->oldPrice, $decision->newPrice)
                    .' / sale '.$this->pair($decision->oldDiscounted, $decision->newDiscounted)
                : '';

            $report[] = [
                $retire ? 'retire' : 'keep',
                $game->id,
                mb_strimwidth($game->title, 0, 45, '…'),
                $decision ? $decision->outcome : 'no record',
                $isFrozen ? 'yes' : '',
                $flags->pluck('flag')->implode(','),
                $priceMoves,
            ];

            if (!$retire) {
                $counts['games kept']++;
                continue;
            }

            if ($isFrozen) {
                $counts['freezes removed']++;
            }
            $counts['flags removed'] += $flags->count();

            if ($apply) {
                if ($isFrozen) {
                    GameImportRuleEshop::where('game_id', $game->id)->update(['ignore_price' => 0]);
                }
                GameFlag::whereIn('id', $flags->pluck('id'))->delete();
            }
        }

        // Kept games first: those are the ones still needing a manual look.
        usort($report, fn ($a, $b) => [$a[0] !== 'keep', $a[1]] <=> [$b[0] !== 'keep', $b[1]]);

        $this->table(['Action', 'Game', 'Title', 'API outcome', 'Frozen', 'Price flags', 'Price after next UpdateGames'], $report);

        $this->newLine();
        foreach ($counts as $label => $count) {
            $this->info(str_pad($label.':', 18).$count);
        }

        $this->newLine();
        if ($apply) {
            $this->info('Applied. The next DSNintendoCoUkUpdateGames sets the API price on the unfrozen games.');
        } else {
            $this->warn('DRY RUN - nothing written. Pass --apply to remove the freezes and flags.');
        }

        return self::SUCCESS;
    }

    private function pair(?string $old, ?string $new): string
    {
        $old = $old ?? '-';
        $new = $new ?? '-';
        return $old === $new ? $old : $old.' -> '.$new;
    }
}
