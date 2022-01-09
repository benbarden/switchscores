<?php


namespace App\Services;

use App\Models\GameTitleHash;


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

    public function edit(
        GameTitleHash $gameTitleHash, $title, $titleHash, $gameId
    )
    {
        $values = [
            'title' => $title,
            'title_hash' => $titleHash,
            'game_id' => $gameId,
        ];

        $gameTitleHash->fill($values);
        $gameTitleHash->save();
    }

    public function delete($titleHashId)
    {
        GameTitleHash::where('id', $titleHashId)->delete();
    }

    public function deleteByGameId($gameId)
    {
        GameTitleHash::where('game_id', $gameId)->delete();
    }

    // ********************************************************** //

    public function find($id): GameTitleHash
    {
        return GameTitleHash::find($id);
    }

    public function generateHash($title): string
    {
        return md5(strtolower($title));
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

    public function getByTitleGroup(array $titles)
    {
        foreach ($titles as &$title) {
            $title = $this->generateHash($title);
        }

        $gameTitleHash = GameTitleHash::whereIn('title_hash', $titles)->get();
        if ($gameTitleHash) {
            return $gameTitleHash->first();
        } else {
            return null;
        }
    }

    public function getByGameId($gameId)
    {
        $titleHashList = GameTitleHash::
            where('game_id', $gameId)
            ->orderBy('id', 'desc')
            ->get();
        return $titleHashList;
    }

    public function getAll()
    {
        $titleHashList = GameTitleHash::
            orderBy('id', 'desc')
            ->get();
        return $titleHashList;
    }
}