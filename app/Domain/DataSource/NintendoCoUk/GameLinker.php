<?php

namespace App\Domain\DataSource\NintendoCoUk;

use App\Domain\DataSourceParsed\Repository as DataSourceParsedRepository;
use App\Domain\Game\Repository as GameRepository;
use App\Factories\DataSource\NintendoCoUk\UpdateGameFactory;
use App\Models\DataSourceParsed;
use App\Models\Game;
use App\Services\Game\Images as GameImages;

/**
 * Links a game to a Nintendo.co.uk API item, or clears its link. Moved unchanged from the
 * Edit link page so Unlinked suggestions (#192) does exactly the same: both call this.
 */
class GameLinker
{
    public function __construct(
        private DataSourceParsedRepository $repoDataSourceParsed,
        private GameRepository $repoGame,
        private DownloadPackshotHelper $downloadPackshotHelper
    )
    {
    }

    /**
     * Pass null as the item to clear the game's link.
     */
    public function link(Game $game, ?DataSourceParsed $dsNewParsedItem): void
    {
        $gameId = $game->id;
        $nintendoCoUkLinkId = $dsNewParsedItem?->link_id;

        $dsCurrentParsedItem = $this->repoDataSourceParsed->getSourceNintendoCoUkForGame($gameId);

        if ($dsCurrentParsedItem) {

            // If game is currently linked to a different API item, we need to break the link
            if ($nintendoCoUkLinkId != $dsCurrentParsedItem->link_id) {

                $dsCurrentParsedItem->game_id = null;
                $dsCurrentParsedItem->save();

                // Only delete the packshots when a replacement link is being set, so the
                // new item's images take their place. Clearing the link entirely used to
                // delete them too, which is destructive and has no way back: the objects
                // are removed from storage, and a game with no link and no store URL has
                // nothing left to re-download from. Unlinking says "this record is not
                // this game", not "this game has no artwork" - and the artwork is usually
                // still right, since the wrong record is normally another edition of the
                // same game.
                if ($nintendoCoUkLinkId) {

                    $serviceGameImages = new GameImages($game);
                    $serviceGameImages->deleteSquare();
                    $serviceGameImages->deleteHeader();

                    $game->image_square = null;
                    $game->image_header = null;

                }

                // Link state goes either way: it describes the link, not the game.
                $game->eshop_europe_fs_id = null;
                $game->save();

            }

        }

        if ($dsNewParsedItem) {

            if ($game->eshop_europe_fs_id != $dsNewParsedItem->link_id) {

                // Link the game to the parsed item (not sure we need a two-way link?)
                $game->eshop_europe_fs_id = $dsNewParsedItem->link_id;

                // Update availability
                $game->format_digital = Game::FORMAT_AVAILABLE;

                $game->save();

            }

            // Link the parsed item to the game
            $dsNewParsedItem->game_id = $gameId;
            $dsNewParsedItem->save();

            UpdateGameFactory::doUpdate($game, $dsNewParsedItem);

            // Download packshots
            $this->downloadPackshotHelper->downloadForGame($game);

        } else {

            // Clear digital status - Removed, we can keep the previous value.
            //$game->format_digital = null;
            // Clear fs_id
            $game->eshop_europe_fs_id = null;
            $game->save();

        }

        // Clear cache
        $this->repoGame->clearCacheCoreData($gameId);
    }
}
