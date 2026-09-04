<?php

namespace App\Listeners;

use App\Events\GameCreated;

/**
 * A newly added game is the one someone is actively working on, so it goes to the
 * front of the crawl queue rather than waiting its turn behind ~15k existing games
 * (GameCrawlBatch orders by crawl_priority DESC, and clears the flag once crawled).
 *
 * This used to be set inline by whichever path happened to remember - the JSON import
 * and the weekly batch importer did; manual add, data source parsed items and the
 * Release Hub did not. Doing it here means any path that creates a game is covered,
 * including new ones (improvement #147).
 */
class PrioritiseNewGameForCrawl
{
    public function handle(GameCreated $event): void
    {
        $game = $event->game;

        if ($game->crawl_priority) {
            return;
        }

        $game->crawl_priority = true;
        $game->save();
    }
}
