<?php

namespace App\Domain\GameGroup;

use App\Models\Console;
use App\Models\Game;
use Illuminate\Support\Collection;

/**
 * Finds likely Switch 1 / Switch 2 pairs among games not in a group yet (#44).
 *
 * Each result is ['switch2' => Game, 'switch1' => Game, 'source' => 'link_title'|'title_key'].
 * A Switch 2 game is only paired when exactly one ungrouped Switch 1 game matches; more
 * than one is counted as ambiguous and left for linking by hand.
 */
class Matcher
{
    const SOURCE_LINK_TITLE = 'link_title';
    const SOURCE_TITLE_KEY = 'title_key';

    private int $ambiguous = 0;

    public function __construct(
        private TitleMatchKey $titleMatchKey
    )
    {
    }

    /**
     * Exact link_title pairs - what the public "Also on" box already trusts.
     */
    public function linkTitlePairs(): Collection
    {
        return $this->findPairs(true);
    }

    /**
     * Every pair, link_title first, then the title key for the rest.
     */
    public function allPairs(): Collection
    {
        return $this->findPairs(false);
    }

    public function ambiguousCount(): int
    {
        return $this->ambiguous;
    }

    private function findPairs(bool $linkTitleOnly): Collection
    {
        $this->ambiguous = 0;

        $switch1 = Game::where('console_id', Console::ID_SWITCH_1)->whereNull('game_group_id')
            ->get(['id', 'title', 'link_title', 'console_id', 'game_status']);
        $switch2 = Game::where('console_id', Console::ID_SWITCH_2)->whereNull('game_group_id')
            ->orderBy('title')
            ->get(['id', 'title', 'link_title', 'console_id', 'game_status']);

        $byLinkTitle = $switch1->groupBy('link_title');
        $byKey = $linkTitleOnly ? collect() : $switch1->groupBy(fn ($game) => $this->titleMatchKey->forTitle($game->title));

        $pairs = collect();
        foreach ($switch2 as $game) {
            $candidates = $byLinkTitle->get($game->link_title, collect());
            $source = self::SOURCE_LINK_TITLE;
            if ($candidates->isEmpty() && !$linkTitleOnly) {
                $candidates = $byKey->get($this->titleMatchKey->forTitle($game->title), collect());
                $source = self::SOURCE_TITLE_KEY;
            }

            if ($candidates->count() > 1) {
                $this->ambiguous++;
            } elseif ($candidates->count() == 1) {
                $pairs->push(['switch2' => $game, 'switch1' => $candidates->first(), 'source' => $source]);
            }
        }

        return $pairs;
    }
}
