<?php

namespace App\Domain\WeeklyBatch;

use App\Domain\Game\Repository as GameRepository;
use App\Domain\GameTitleHash\HashGenerator;
use App\Domain\GameTitleHash\Repository as GameTitleHashRepository;
use App\Models\Game;

/**
 * Finds the game already in the DB for a weekly update item, if there is one (#211).
 *
 * Used when a page is parsed, and again just before import, since the title may have been
 * edited by hand in between (#199). Tries, in order:
 *
 * 1. The NSUID, the only stable ID - when the paste supplied one and the game has it.
 * 2. The title hashes for the item's title and the raw pasted title. A game renamed after
 *    import keeps its old hash, and the raw title is often what it was renamed to.
 * 3. The game's current title, for any game missing its hash.
 */
class ExistingGameMatcher
{
    public function __construct(
        private GameRepository $repoGame,
        private GameTitleHashRepository $repoTitleHash,
        private HashGenerator $hashGenerator
    ) {
    }

    public function find(?string $nsuid, string $title, ?string $titleRaw, int $consoleId): ?Game
    {
        if ($nsuid) {
            $byNsuid = $this->repoGame->getByEshopEuropeNsuid($nsuid, $consoleId);
            if ($byNsuid) {
                return $byNsuid;
            }
        }

        foreach (array_unique(array_filter([$title, $titleRaw])) as $candidate) {
            $hash = $this->repoTitleHash->getByHashAndConsole($this->hashGenerator->generateHash($candidate), $consoleId);
            if ($hash && $hash->game) {
                return $hash->game;
            }
        }

        return $this->repoGame->getByTitleAndConsole($title, $consoleId);
    }
}
