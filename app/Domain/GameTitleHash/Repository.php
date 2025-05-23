<?php


namespace App\Domain\GameTitleHash;

use App\Models\GameTitleHash;

class Repository
{
    public function create(
        $title, $titleHash, $gameId
    )
    {
        $title = strtolower($title);

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

    public function find($id): GameTitleHash
    {
        return GameTitleHash::find($id);
    }

    /**
     * @param $hash
     * @return bool
     */
    public function titleHashExists($hash): bool
    {
        $titleHash = GameTitleHash::where('title_hash', $hash)->first();
        return $titleHash != null;
    }

    public function byTitleGroup(array $titles)
    {
        $hashGenerator = new HashGenerator();

        foreach ($titles as &$title) {
            $title = $hashGenerator->generateHash($title);
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
        return GameTitleHash::where('game_id', $gameId)->orderBy('id', 'desc')->get();
    }

    public function getAll()
    {
        return GameTitleHash::orderBy('id', 'desc')->get();
    }
}