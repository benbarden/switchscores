<?php

namespace App\Http\Controllers\Api\Game;

use App\Models\Game;

use App\Domain\Game\Repository as GameRepository;
use App\Domain\GameLists\Repository as GameListsRepository;

class BulkUpdate
{
    protected $repoGame;
    protected $repoGameLists;

    public function __construct(
        GameRepository $repoGame,
        GameListsRepository $repoGameLists
    )
    {
        $this->repoGame = $repoGame;
        $this->repoGameLists = $repoGameLists;
    }
    public function eshopUpcomingCrosscheck()
    {
        $request = request();
        $postData = $request->post();

        $fieldToUpdate = 'eshop_europe_order';

        foreach ($postData as $pdk => $pdv) {

            if ($pdk == '_token') continue;

            $gameId = str_replace($fieldToUpdate.'_', '', $pdk);
            $game = $this->repoGame->find($gameId);
            if (!$game) abort(400);

            $game->{$fieldToUpdate} = $pdv;
            $game->save();

        }

        $bindings['GameList'] = $this->repoGameLists->upcomingEshopCrosscheck();
        $outputHtml = view('components.staff.games.bulk-edit.table', $bindings);
        return $outputHtml;
    }
}
