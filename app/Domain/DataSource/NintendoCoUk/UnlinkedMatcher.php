<?php

namespace App\Domain\DataSource\NintendoCoUk;

use App\Domain\GameGroup\TitleMatchKey;
use App\Enums\GameStatus;
use App\Models\Console;
use App\Models\DataSourceParsed;
use App\Models\Game;
use Illuminate\Support\Collection;

/**
 * Suggests games for unlinked Nintendo.co.uk API items (#192).
 *
 * An item is only matched to a game on the same console that has no API link yet, by
 * TitleMatchKey. Each item lands in one of three lists:
 * - matches: exactly one game fits, ['item' => DataSourceParsed, 'game' => Game]
 * - several: more than one fits, ['item' => DataSourceParsed, 'games' => Collection]
 * - none: nothing fits, ['item' => DataSourceParsed, 'linkedGame' => ?Game, 'otherConsoleGame' => ?Game].
 *   linkedGame is a game on the same console with the same title that is already linked to
 *   another API item, so adding it again would be a duplicate. otherConsoleGame is the game
 *   with the same title on the other console, as a hint when adding.
 */
class UnlinkedMatcher
{
    public function __construct(
        private TitleMatchKey $titleMatchKey
    )
    {
    }

    public function suggest(Collection $items): array
    {
        $games = Game::where('game_status', '!=', GameStatus::SOFT_DELETED)
            ->get(['id', 'title', 'console_id', 'eshop_europe_fs_id', 'eu_release_date']);

        $byKey = $games->groupBy(fn ($game) => $this->key($game->console_id, $game->title));

        $result = ['matches' => [], 'several' => [], 'none' => []];

        foreach ($items as $item) {
            $sameTitle = $byKey->get($this->key($item->console_id, $item->title), collect());
            $candidates = $sameTitle->whereNull('eshop_europe_fs_id')->values();

            if ($candidates->count() == 1) {
                $result['matches'][] = ['item' => $item, 'game' => $candidates->first()];
            } elseif ($candidates->count() > 1) {
                $result['several'][] = ['item' => $item, 'games' => $candidates];
            } else {
                $otherConsoleId = $item->console_id == Console::ID_SWITCH_2 ? Console::ID_SWITCH_1 : Console::ID_SWITCH_2;
                $result['none'][] = [
                    'item' => $item,
                    'linkedGame' => $sameTitle->first(),
                    'otherConsoleGame' => $byKey->get($this->key($otherConsoleId, $item->title), collect())->first(),
                ];
            }
        }

        return $result;
    }

    private function key($consoleId, string $title): string
    {
        return $consoleId.'|'.$this->titleMatchKey->forTitle($title);
    }
}
