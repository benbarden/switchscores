<?php

namespace App\Http\Controllers\Api\Admin;

use App\Traits\SwitchServices;

class Developer
{
    use SwitchServices;

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

        $serviceGame = $this->getServiceGame();
        $servicePartner = $this->getServicePartner();
        $serviceGameDeveloper = $this->getServiceGameDeveloper();

        // Validation
        $game = $serviceGame->find($gameId);
        if (!$game) {
            return response()->json(['error' => 'Game not found: '.$gameId], 400);
        }

        $partner = $servicePartner->find($developerId);
        if (!$partner) {
            return response()->json(['error' => 'Developer not found: '.$developerId], 400);
        }

        if ($serviceGameDeveloper->gameHasDeveloper($gameId, $developerId)) {
            return response()->json(['error' => 'Game already linked to developer'], 400);
        }

        // All OK - add to game
        $serviceGameDeveloper->createGameDeveloper($gameId, $developerId);

        return response()->json(['message' => 'Success'], 200);
    }
}
