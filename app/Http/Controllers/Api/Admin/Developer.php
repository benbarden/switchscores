<?php

namespace App\Http\Controllers\Api\Admin;

use App\Domain\Game\Repository as GameRepository;
use App\Domain\GamesCompany\Repository as GamesCompanyRepository;
use App\Domain\GameDeveloper\Repository as GameDeveloperRepository;

class Developer
{
    public function __construct(
        private GameRepository $repoGame,
        private GamesCompanyRepository $repoGamesCompany,
        private GameDeveloperRepository $repoGameDeveloper
    )
    {
    }

    public function addGameDeveloper()
    {
        $request = request();

        $gameId = $request->gameId;
        $developerId = $request->developerId;

        if (!$gameId) {
            return response()->json(['error' => 'Missing data: gameId'], 400);
        }
        if (!$developerId) {
            return response()->json(['error' => 'Missing data: developerId'], 400);
        }

        // Validation
        $game = $this->repoGame->find($gameId);
        if (!$game) {
            return response()->json(['error' => 'Game not found: '.$gameId], 400);
        }

        $gamesCompany = $this->repoGamesCompany->find($developerId);
        if (!$gamesCompany) {
            return response()->json(['error' => 'Developer not found: '.$developerId], 400);
        }

        if ($this->repoGameDeveloper->gameHasDeveloper($gameId, $developerId)) {
            return response()->json(['error' => 'Game already linked to developer'], 400);
        }

        // All OK - add to game
        $this->repoGameDeveloper->create($gameId, $developerId);

        return response()->json(['message' => 'Success'], 200);
    }
}
