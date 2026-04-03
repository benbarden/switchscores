<?php


namespace App\Domain\GameTitleHash;

use App\Models\GameTitleHash;

class Repository
{
    public function create(
        $title, $titleHash, $gameId, $consoleId
    )
    {
        $title = strtolower($title);

        return GameTitleHash::create([
            'title' => $title,
            'title_hash' => $titleHash,
            'game_id' => $gameId,
            'console_id' => $consoleId,
        ]);
    }

    public function edit(
        GameTitleHash $gameTitleHash, $title, $titleHash, $gameId, $consoleId
    )
    {
        $values = [
            'title' => $title,
            'title_hash' => $titleHash,
            'game_id' => $gameId,
            'console_id' => $consoleId,
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
     * @deprecated Use titleHashExistsForConsole() instead
     */
    public function titleHashExists($hash): bool
    {
        $titleHash = GameTitleHash::where('title_hash', $hash)->first();
        return $titleHash != null;
    }

    /**
     * Check if a title hash exists for a specific console
     */
    public function titleHashExistsForConsole($hash, $consoleId): bool
    {
        return GameTitleHash::where('title_hash', $hash)
            ->where('console_id', $consoleId)
            ->exists();
    }

    /**
     * @deprecated Use hashExistsForOtherGameOnConsole() instead
     */
    public function hashExistsForOtherGame($hash, $excludeGameId): bool
    {
        return GameTitleHash::where('title_hash', $hash)
            ->where('game_id', '!=', $excludeGameId)
            ->exists();
    }

    /**
     * Check if a title hash exists for another game on the same console
     */
    public function hashExistsForOtherGameOnConsole($hash, $excludeGameId, $consoleId): bool
    {
        return GameTitleHash::where('title_hash', $hash)
            ->where('game_id', '!=', $excludeGameId)
            ->where('console_id', $consoleId)
            ->exists();
    }

    public function hashExistsForGame($hash, $gameId): bool
    {
        return GameTitleHash::where('title_hash', $hash)
            ->where('game_id', $gameId)
            ->exists();
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