<?php

namespace App\Http\Controllers\Api\V2\Game;

use App\Domain\Game\Repository as GameRepository;
use Illuminate\Http\Request;

class SearchController
{
    public function __construct(
        private GameRepository $repoGame,
    )
    {
    }

    public function findByTitle(Request $request)
    {
        $query = $request->query('q');
        if (!$query) {
            return response()->json(['message' => 'Missing query string'], 400);
        }

        $games = $this->repoGame->partialTitleSearch($query);

        return response()->json(['games' => $games], 200);
    }
}
