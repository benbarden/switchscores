<?php

namespace App\Domain\UserIgnoredGames;

use App\Models\UserIgnoredGame;

class Repository
{
    public function add($userId, $gameId)
    {
        return UserIgnoredGame::create([
            'user_id' => $userId,
            'game_id' => $gameId,
        ]);
    }

    public function delete($id)
    {
        UserIgnoredGame::where('id', $id)->delete();
    }

    public function deleteByUserAndGame($userId, $gameId)
    {
        UserIgnoredGame::where('user_id', $userId)->where('game_id', $gameId)->delete();
    }

    public function find($id)
    {
        return UserIgnoredGame::find($id);
    }

    public function byUser($userId, $limit = null)
    {
        $query = UserIgnoredGame::with('game', 'game.category')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    public function byUserGameIds($userId)
    {
        return UserIgnoredGame::where('user_id', $userId)->pluck('game_id');
    }

    public function isGameIgnored($userId, $gameId)
    {
        return UserIgnoredGame::where('user_id', $userId)->where('game_id', $gameId)->exists();
    }

    public function countByUser($userId)
    {
        return UserIgnoredGame::where('user_id', $userId)->count();
    }
}
