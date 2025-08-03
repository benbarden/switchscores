<?php

namespace App\Http\Controllers\Api\V2\User;

use App\Domain\Game\Repository as GameRepository;
use App\Domain\UserGamesCollection\Repository as UserGamesCollectionRepository;
use App\Services\Game\Images as GameImages;
use App\Models\Game;
use Illuminate\Http\Request;

class CollectionController
{
    public function __construct(
        private GameRepository $repoGame,
        private UserGamesCollectionRepository $repoUserGamesCollection,
    )
    {
    }

    public function findGamesForAdding(Request $request)
    {
        $query = $request->query('q');
        if (!$query) {
            return response()->json(['message' => 'Missing query string'], 400);
        }

        $qSortBy = $request->query('sortBy');
        match ($qSortBy) {
            'title' => $dbSortBy = 'title',
            'newest' => $dbSortBy = 'newest',
            default => $dbSortBy = 'newest',
        };

        $qIncludeLowQuality = $request->query('includeLowQuality') == 1 ? true : false;
        $qIncludeDeListed = $request->query('includeDeListed') == 1 ? true : false;

        $userId = auth()->id();

        $games = $this->repoGame->partialTitleSearch($query, $qIncludeLowQuality, $qIncludeDeListed, $dbSortBy, 20);

        $collectionGameIds = $this->repoUserGamesCollection->byUserGameIds($userId);

        $gameResults = [];

        foreach ($games as $game) {
            $inCollection = $collectionGameIds->contains($game->id);
            if ($game->image_square) {
                $squareImageUrl = GameImages::PATH_IMAGE_SQUARE.$game->image_square;
            } else {
                $squareImageUrl = null;
            }
            $gameResults[] = [
                'id' => $game->id,
                'title' => $game->title,
                'eu_release_date' => date('d M Y', strtotime($game->eu_release_date)),
                'inCollection' => $inCollection,
                'squareImageUrl' => $squareImageUrl,
                'isLowQuality' => (int) $game->is_low_quality,
                'isDeListed' => $game->format_digital == Game::FORMAT_DELISTED ? 1 : 0,
                'consoleName' => $game->console->name,
            ];
        }

        return response()->json(['games' => $gameResults], 200);
    }
}
