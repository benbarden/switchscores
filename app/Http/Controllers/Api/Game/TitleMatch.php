<?php

namespace App\Http\Controllers\Api\Game;

use App\Services\Game\TitleMatch as ServiceTitleMatch;

use App\Domain\Game\Repository as GameRepository;

use App\Traits\SwitchServices;

class TitleMatch
{
    use SwitchServices;

    protected $repoGame;

    public function __construct(
        GameRepository $repoGame
    )
    {
        $this->repoGame = $repoGame;
    }

    public function getByExactTitleMatch()
    {
        $request = request();

        $title = $request->title;
        $gameId = $request->gameId;

        if (!$title) {
            return response()->json(['error' => 'Missing data: title'], 400);
        }

        $titleExists = $this->repoGame->titleExists($title, $gameId);
        if ($titleExists) {
            $existingGame = $this->repoGame->getByTitle($title);
            return response()->json(['gameId' => $existingGame->id], 200);
        } else {
            return response()->json(['gameId' => null], 404);
        }
    }

    public function getByTitle()
    {
        $serviceGame = $this->getServiceGame();

        $request = request();

        $title = $request->title;
        $matchRule = $request->matchRule;
        $matchIndex = $request->matchIndex;

        if (!$title) {
            return response()->json(['error' => 'Missing data: title'], 400);
        }
        if (!$matchRule) {
            $matchRule = '';
            $matchIndex = 0;
        }

        $serviceTitleMatch = new ServiceTitleMatch();
        $serviceTitleMatch->setMatchRule($matchRule);
        $serviceTitleMatch->setMatchIndex($matchIndex);
        $matchedTitle = $serviceTitleMatch->generate($title);

        $game = $serviceGame->getByTitle($matchedTitle);
        if ($game) {
            return response()->json(['id' => $game->id], 200);
        } else {
            return response()->json(['message' => 'Not found'], 404);
        }
    }
}
