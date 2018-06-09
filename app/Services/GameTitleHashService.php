<?php


namespace App\Services;

use App\GameTitleHash;
use Illuminate\Support\Facades\DB;


class GameTitleHashService
{
    public function create(
        $title, $titleHash, $gameId
    )
    {
        return GameTitleHash::create([
            'title' => $title,
            'title_hash' => $titleHash,
            'game_id' => $gameId,
        ]);
    }

    public function deleteByGameId($gameId)
    {
        GameTitleHash::where('game_id', $gameId)->delete();
    }

    // ********************************************************** //

    public function generateHash($title)
    {
        return md5($title);
    }

    public function getByHash($hash)
    {
        $gameTitleHash = GameTitleHash::where('title_hash', $hash)->get();
        if ($gameTitleHash) {
            return $gameTitleHash->first();
        } else {
            return null;
        }
    }
}