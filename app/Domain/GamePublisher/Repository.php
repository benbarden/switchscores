<?php

namespace App\Domain\GamePublisher;

use App\Models\GamePublisher;

class Repository
{
    public function create($gameId, $publisherId)
    {
        GamePublisher::create([
            'game_id' => $gameId,
            'publisher_id' => $publisherId
        ]);
    }

}