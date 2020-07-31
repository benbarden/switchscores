<?php

namespace App\Http\Controllers\Api\Game;

use App\Traits\SwitchServices;

class GameController
{
    use SwitchServices;

    public function getList()
    {
        $gameList = $this->getServiceGame()->getApiIdList();

        if ($gameList) {
            return response()->json(['games' => $gameList], 200);
        } else {
            return response()->json(['message' => 'Error retrieving game list'], 400);
        }
    }
}
