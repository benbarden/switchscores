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

    /**
     * The single game a set of title variants points to, or null.
     *
     * Null when nothing matches, and also when the hashes point to more than one game - the
     * same title on both consoles, most often. Picking one there would publish a review on
     * whichever game the database happened to return first, so an ambiguous title is left
     * unmatched for a human instead. Passing a console narrows the lookup to that console's
     * games, which resolves the common case.
     */
    public function byTitleGroup(array $titles, $consoleId = null)
    {
        $gameTitleHashes = $this->allByTitleGroup($titles, $consoleId);

        if ($gameTitleHashes->pluck('game_id')->unique()->count() != 1) {
            return null;
        }

        return $gameTitleHashes->first();
    }

    /**
     * Every title hash matching a set of title variants, optionally limited to one console.
     * For callers that need to tell "no match" apart from "more than one game".
     */
    public function allByTitleGroup(array $titles, $consoleId = null)
    {
        $hashGenerator = new HashGenerator();

        foreach ($titles as &$title) {
            $title = $hashGenerator->generateHash($title);
        }

        $query = GameTitleHash::whereIn('title_hash', $titles);

        if ($consoleId) {
            $query->where('console_id', $consoleId);
        }

        return $query->get();
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