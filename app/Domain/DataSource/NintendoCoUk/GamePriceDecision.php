<?php

namespace App\Domain\DataSource\NintendoCoUk;

use App\Models\Console;
use App\Models\DataSource;
use App\Models\DataSourceParsed;
use App\Models\DataSourcePrice;
use App\Models\DataSourceRaw;
use App\Models\Game;
use App\Models\GameImportRuleEshop;
use Carbon\Carbon;

/**
 * Finds a game's stored SKU prices and asks PriceApply for its decision (#182).
 *
 * DSNintendoCoUkApplyPrices loads every Switch 2 game in bulk and uses skuPrices()
 * for each one; the staff game detail page uses forGame() for one game. Both pick
 * SKUs the same way, so the banner on the detail page says what tonight's run will do.
 */
class GamePriceDecision
{
    /**
     * Same as the --max-age default on DSNintendoCoUkApplyPrices.
     */
    const MAX_AGE_HOURS = 36;

    public function __construct(
        private NsuidExtractor $extractor,
        private PriceApply $priceApply
    ) {}

    /**
     * The stored prices for the NSUIDs in one raw record.
     *
     * @param  iterable<string, DataSourcePrice>|\ArrayAccess  $pricesByNsuid  keyed by nsuid
     * @return DataSourcePrice[]
     */
    public function skuPrices(?string $rawJson, $pricesByNsuid): array
    {
        $skuPrices = [];
        foreach ($this->extractor->extractFromJson($rawJson) as $nsuid) {
            if (isset($pricesByNsuid[$nsuid])) {
                $skuPrices[] = $pricesByNsuid[$nsuid];
            }
        }

        return $skuPrices;
    }

    /**
     * The decision for one Switch 2 game, or null when ApplyPrices does not look at
     * this game at all (Switch 1, or no linked Nintendo.co.uk record).
     *
     * $ignoreFreeze decides as if the game had no ignore_price rule - what the price
     * would be if the freeze were removed (DSNintendoCoUkRetirePriceHolds).
     */
    public function forGame(Game $game, ?Carbon $now = null, bool $ignoreFreeze = false): ?PriceDecision
    {
        if ((int) $game->console_id !== Console::ID_SWITCH_2) {
            return null;
        }

        // Same filter as ApplyPrices: orphan rows with no link_id can't be matched to a raw record
        $parsed = DataSourceParsed::where('source_id', DataSource::DSID_NINTENDO_CO_UK)
            ->where('console_id', Console::ID_SWITCH_2)
            ->where('game_id', $game->id)
            ->whereNotNull('link_id')
            ->first(['game_id', 'link_id']);
        if (!$parsed) {
            return null;
        }

        $rawJson = DataSourceRaw::where('source_id', DataSource::DSID_NINTENDO_CO_UK)
            ->where('console_id', Console::ID_SWITCH_2)
            ->where('link_id', $parsed->link_id)
            ->value('source_data_json');

        $nsuids = $this->extractor->extractFromJson($rawJson);
        $prices = DataSourcePrice::where('console_id', Console::ID_SWITCH_2)
            ->whereIn('nsuid', $nsuids)
            ->get()
            ->keyBy('nsuid');

        $rule = $ignoreFreeze ? null : GameImportRuleEshop::where('game_id', $game->id)->first();

        return $this->priceApply->decide(
            $game,
            $this->skuPrices($rawJson, $prices),
            $rule,
            $now ?? Carbon::now(),
            self::MAX_AGE_HOURS
        );
    }
}
