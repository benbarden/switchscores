<?php

namespace App\Domain\GameFlag;

use App\Models\Game;
use App\Models\GameFlag;

class Repository
{
    public function getByGameId($gameId)
    {
        return GameFlag::where('game_id', $gameId)->orderBy('flag')->get();
    }

    public function find($id)
    {
        return GameFlag::find($id);
    }

    public function getAllFlagsWithCount()
    {
        return GameFlag::select('flag', \Illuminate\Support\Facades\DB::raw('count(*) as game_count'))
            ->groupBy('flag')
            ->orderBy('flag')
            ->get();
    }

    public function getGamesByFlag(string $flag)
    {
        return Game::whereHas('gameFlags', function ($q) use ($flag) {
            $q->where('flag', $flag);
        })->orderBy('title')->get();
    }

    public function add($gameId, string $flag, ?string $notes = null): GameFlag
    {
        return GameFlag::firstOrCreate(
            ['game_id' => $gameId, 'flag' => $flag],
            ['notes' => $notes]
        );
    }

    public function remove($id): void
    {
        GameFlag::destroy($id);
    }
}
