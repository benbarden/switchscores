<?php

namespace App\Http\Controllers\Api\Game;

use App\Models\Game;

use App\Domain\Game\Repository as GameRepository;
use App\Domain\GameLists\Repository as GameListsRepository;

class BulkUpdate
{
    public function __construct(
        private GameRepository $repoGame,
        private GameListsRepository $repoGameLists
    )
    {
    }
    public function eshopUpcomingCrosscheck()
    {
        $request = request();
        $postData = $request->post();

        $consoleId = $postData['console_id'];

        $fieldToUpdate = 'eshop_europe_order';

        foreach ($postData as $pdk => $pdv) {

            if (in_array($pdk, ['_token', 'console_id'])) continue;

            $gameId = str_replace($fieldToUpdate.'_', '', $pdk);
            $game = $this->repoGame->find($gameId);
            if (!$game) {
                throw new \Exception('Game not found: game_id ['.$gameId.']');
            }

            $game->{$fieldToUpdate} = $pdv;
            $game->save();

        }

        $bindings['GameList'] = $this->repoGameLists->upcomingEshopCrosscheck($consoleId);
        $bindings['ConsoleId'] = $consoleId;
        $outputHtml = view('components.staff.games.bulk-edit.table', $bindings);
        return $outputHtml;
    }
}
