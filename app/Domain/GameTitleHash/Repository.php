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
}