<?php

namespace App\Domain\UserWishlist;

use App\Models\UserWishlist;

class Repository
{
    public function add($userId, $gameId)
    {
        return UserWishlist::create([
            'user_id' => $userId,
            'game_id' => $gameId,
        ]);
    }

    public function delete($id)
    {
        UserWishlist::where('id', $id)->delete();
    }

    public function deleteByUserAndGame($userId, $gameId)
    {
        UserWishlist::where('user_id', $userId)->where('game_id', $gameId)->delete();
    }

    public function find($id)
    {
        return UserWishlist::find($id);
    }

    public function byUser($userId, $limit = null)
    {
        $query = UserWishlist::with('game', 'game.category')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    public function byUserGameIds($userId)
    {
        return UserWishlist::where('user_id', $userId)->pluck('game_id');
    }

    public function isGameInWishlist($userId, $gameId)
    {
        return UserWishlist::where('user_id', $userId)->where('game_id', $gameId)->exists();
    }

    public function countByUser($userId)
    {
        return UserWishlist::where('user_id', $userId)->count();
    }
}
