<?php

namespace App\Domain\GameGroup;

use App\Models\Game;
use App\Models\GameGroup;

class Repository
{
    public function create(string $name, string $matchType, ?string $note = null): GameGroup
    {
        return GameGroup::create([
            'name' => $name,
            'match_type' => $matchType,
            'note' => $note,
        ]);
    }

    public function find($id)
    {
        return GameGroup::find($id);
    }

    public function getAll()
    {
        return GameGroup::with('games.console')->orderBy('name')->get();
    }

    public function linkGame(GameGroup $group, Game $game): void
    {
        $game->game_group_id = $group->id;
        $game->save();
    }

    /**
     * A group means "the same game on more than one console", so once fewer than two
     * games are left in it, the group goes and the remaining game is unlinked too.
     */
    public function unlinkGame(Game $game): void
    {
        $group = $game->gameGroup;
        if (!$group) return;

        $game->game_group_id = null;
        $game->save();

        $remaining = $group->games()->get();
        if ($remaining->count() < 2) {
            foreach ($remaining as $other) {
                $other->game_group_id = null;
                $other->save();
            }
            $group->delete();
        }
    }
}
