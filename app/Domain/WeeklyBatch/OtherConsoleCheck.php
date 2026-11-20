<?php

namespace App\Domain\WeeklyBatch;

use App\Domain\DataSourceParsed\Repository as DataSourceParsedRepository;
use App\Domain\DataSourceRaw\Repository as DataSourceRawRepository;
use App\Models\Console;
use App\Models\DataSource;
use App\Models\WeeklyBatchItem;
use Illuminate\Support\Collection;

/**
 * Warns when a row on a Switch 2 list is really a Switch 1 game. Nintendo can tag a Switch 1
 * game as Switch 2 too, so it shows up in the Switch 2 store listings, and as the existing-game
 * match only looks on the same console, it's offered as a new game.
 *
 * Signals, all specific to this case:
 * - the store page's Nintendo UK API record is linked to a Switch 1 game
 * - the record lists both systems, isn't linked, and the URL isn't in a Switch 2 section
 *   (Switch 2 Editions can carry both tags)
 * - the store URL is in the Switch 1 games section (/Nintendo-Switch-games/)
 *
 * A matching title on Switch 1 isn't used: most Switch 2 games with one are real Switch 2
 * versions. The warning doesn't block anything; staff decide.
 */
class OtherConsoleCheck
{
    private const SWITCH_1_PATH = '/Nintendo-Switch-games/';
    private const SWITCH_2_PATHS = ['/Nintendo-Switch-2-games/', '/Nintendo-Switch-2-Edition/'];

    public function __construct(
        private DataSourceRawRepository $repoRaw,
        private DataSourceParsedRepository $repoParsed
    ) {
    }

    /**
     * @param Collection<WeeklyBatchItem> $items
     * @return array<int, string> item ID => warning
     */
    public function warnings(Collection $items): array
    {
        $items = $items->filter(fn($i) => $i->console === 'switch-2' && $i->nintendo_url);

        $pageIds = [];
        foreach ($items as $item) {
            $pageId = self::storePageId($item->nintendo_url);
            if ($pageId) {
                $pageIds[$item->id] = $pageId;
            }
        }

        $uniquePageIds = array_values(array_unique($pageIds));
        $dualTagged = $this->repoRaw
            ->getBySourceIdAndLinkIds(DataSource::DSID_NINTENDO_CO_UK, $uniquePageIds)
            ->filter(fn($raw) => self::listsBothSystems($raw->source_data_json))
            ->keyBy('link_id');

        $linkedGames = empty($uniquePageIds) ? collect() : $this->repoParsed
            ->getAllNintendoCoUkInLinkIdList($uniquePageIds)
            ->whereNotNull('game_id')
            ->load('game')
            ->filter(fn($parsed) => $parsed->game)
            ->keyBy('link_id')
            ->map(fn($parsed) => $parsed->game);

        $warnings = [];
        foreach ($items as $item) {
            $pageId = $pageIds[$item->id] ?? null;
            $onSwitch1Path = str_contains($item->nintendo_url, self::SWITCH_1_PATH);

            $game = $pageId ? $linkedGames->get($pageId) : null;

            if ($game && (int) $game->console_id === Console::ID_SWITCH_1) {
                $warnings[$item->id] = "Store page is the Switch 1 game {$game->title} ({$game->id})";
            } elseif ($pageId && $dualTagged->has($pageId) && !$game && !self::onSwitch2Path($item->nintendo_url)) {
                // Switch 2 Editions can carry both tags too, so an unlinked record only counts
                // when the URL isn't in a Switch 2 section
                $warnings[$item->id] = 'Store page is tagged for Switch 1 and Switch 2';
            } elseif ($onSwitch1Path) {
                $warnings[$item->id] = 'Store page is in the Switch 1 games section';
            }
        }

        return $warnings;
    }

    /**
     * The store page ID at the end of a Nintendo UK URL: ...-2875478.html gives 2875478.
     */
    public static function storePageId(string $url): ?string
    {
        return preg_match('/-(\d+)\.html$/', $url, $m) ? $m[1] : null;
    }

    private static function onSwitch2Path(string $url): bool
    {
        foreach (self::SWITCH_2_PATHS as $path) {
            if (str_contains($url, $path)) return true;
        }
        return false;
    }

    private static function listsBothSystems(?string $json): bool
    {
        $systems = json_decode((string) $json, true)['system_names_txt'] ?? [];
        if (!is_array($systems)) return false;

        return in_array('Nintendo Switch', $systems, true) && in_array('Nintendo Switch 2', $systems, true);
    }
}
