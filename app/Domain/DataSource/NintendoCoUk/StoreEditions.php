<?php

namespace App\Domain\DataSource\NintendoCoUk;

use App\Models\DataSource;
use App\Models\DataSourceParsed;
use App\Models\DataSourcePrice;
use App\Models\DataSourceRaw;
use App\Models\Game;

/**
 * A game's purchase options on the Nintendo store, from the price API data.
 *
 * The NSUIDs in the game's raw API record are its SKUs, and data_source_prices holds each
 * one's price. The NSUID prefix gives the type: 7001 the standard game, 7005 an upgrade pack
 * (Switch 2 Edition upgrades), 7007 a deluxe edition or bundle. On Switch 1, deluxe, ultimate
 * and bundle editions all share 7007, so the type says "an edition" but not which one.
 *
 * Read only, both consoles. Prices are as fresh as the last DSNintendoCoUkFetchPrices run for
 * the console, which is nightly for both.
 */
class StoreEditions
{
    const TYPE_STANDARD = 'standard';
    const TYPE_UPGRADE  = 'upgrade';
    const TYPE_EDITION  = 'edition';
    const TYPE_OTHER    = 'other';

    const LABELS = [
        self::TYPE_STANDARD => 'Standard',
        self::TYPE_UPGRADE  => 'Upgrade pack',
        self::TYPE_EDITION  => 'Deluxe edition or bundle',
        self::TYPE_OTHER    => 'Other',
    ];

    public function __construct(private NsuidExtractor $extractor)
    {
    }

    public static function typeFor(string $nsuid): string
    {
        return match (true) {
            str_starts_with($nsuid, NsuidPrice::PREFIX_GAME)    => self::TYPE_STANDARD,
            str_starts_with($nsuid, NsuidPrice::PREFIX_UPGRADE) => self::TYPE_UPGRADE,
            str_starts_with($nsuid, NsuidPrice::PREFIX_DELUXE)  => self::TYPE_EDITION,
            default                                             => self::TYPE_OTHER,
        };
    }

    /**
     * Every SKU on the game's raw record, standard first. A SKU with no stored price (not
     * fetched yet) is still listed, with price null.
     *
     * @return array<int, array{nsuid: string, type: string, label: string, price: ?DataSourcePrice}>
     */
    public function forGame(Game $game): array
    {
        $parsed = DataSourceParsed::where('source_id', DataSource::DSID_NINTENDO_CO_UK)
            ->where('console_id', $game->console_id)
            ->where('game_id', $game->id)
            ->whereNotNull('link_id')
            ->first(['link_id']);
        if (!$parsed) {
            return [];
        }

        $rawJson = DataSourceRaw::where('source_id', DataSource::DSID_NINTENDO_CO_UK)
            ->where('console_id', $game->console_id)
            ->where('link_id', $parsed->link_id)
            ->value('source_data_json');

        $nsuids = $this->extractor->extractFromJson($rawJson);
        $prices = DataSourcePrice::whereIn('nsuid', $nsuids)->get()->keyBy('nsuid');

        return $this->rows($nsuids, $prices);
    }

    /**
     * The lowest current price among the game's other editions, for the public game page; null
     * if there are none to show. A live sale counts, as it does for the main price.
     *
     * Only deluxe editions and bundles count: an upgrade pack isn't another edition of the
     * game. A game with no standard SKU is a standalone bundle, so its 7007 SKU is the game
     * itself, not another edition of it.
     */
    public function otherEditionsFrom(Game $game): ?float
    {
        $hasStandard = false;
        $prices = [];
        foreach ($this->forGame($game) as $row) {
            if ($row['type'] === self::TYPE_STANDARD) {
                $hasStandard = true;
                continue;
            }
            $price = $row['price'];
            if ($row['type'] !== self::TYPE_EDITION || !$price) continue;
            if (!in_array($price->sales_status, PriceApply::USABLE_STATUSES, true)) continue;

            $prices[] = (float) ($price->hasLiveDiscount() ? $price->discount_price : $price->regular_price);
        }

        if (!$hasStandard || !$prices) {
            return null;
        }

        return min($prices);
    }

    /**
     * Linked games on a console that have more than the standard SKU: a deluxe edition,
     * bundle or upgrade pack. For the staff list.
     *
     * Reads the raw records in chunks: Switch 1 has about 9,000, too many to hold at once
     * in a web request.
     *
     * @return array<int, array{game: Game, skus: array}>
     */
    public function gamesWithEditions(int $consoleId): array
    {
        $linkToGame = DataSourceParsed::where('source_id', DataSource::DSID_NINTENDO_CO_UK)
            ->where('console_id', $consoleId)
            ->whereNotNull('game_id')
            ->whereNotNull('link_id')
            ->pluck('game_id', 'link_id');

        $nsuidsByGame = [];
        DataSourceRaw::where('source_id', DataSource::DSID_NINTENDO_CO_UK)
            ->where('console_id', $consoleId)
            ->where('is_delisted', 0)
            ->select(['id', 'link_id', 'source_data_json'])
            ->chunkById(500, function ($records) use ($linkToGame, &$nsuidsByGame) {
                foreach ($records as $record) {
                    $gameId = $linkToGame[$record->link_id] ?? null;
                    if (!$gameId || isset($nsuidsByGame[$gameId])) continue;

                    $nsuids = $this->extractor->extractFromJson($record->source_data_json);
                    foreach ($nsuids as $nsuid) {
                        if (self::typeFor($nsuid) !== self::TYPE_STANDARD) {
                            $nsuidsByGame[$gameId] = $nsuids;
                            break;
                        }
                    }
                }
            });

        if (!$nsuidsByGame) {
            return [];
        }

        $prices = DataSourcePrice::whereIn('nsuid', array_merge(...array_values($nsuidsByGame)))->get()->keyBy('nsuid');
        $games = Game::whereIn('id', array_keys($nsuidsByGame))->orderBy('title')->get();

        return $games->map(fn (Game $game) => [
            'game' => $game,
            'skus' => $this->rows($nsuidsByGame[$game->id], $prices),
        ])->all();
    }

    private function rows(array $nsuids, $prices): array
    {
        $order = [self::TYPE_STANDARD => 0, self::TYPE_EDITION => 1, self::TYPE_UPGRADE => 2, self::TYPE_OTHER => 3];

        $rows = array_map(function ($nsuid) use ($prices) {
            $type = self::typeFor($nsuid);
            return [
                'nsuid' => $nsuid,
                'type' => $type,
                'label' => self::LABELS[$type],
                'price' => $prices[$nsuid] ?? null,
            ];
        }, $nsuids);

        usort($rows, fn ($a, $b) => [$order[$a['type']], $a['nsuid']] <=> [$order[$b['type']], $b['nsuid']]);

        return $rows;
    }
}
