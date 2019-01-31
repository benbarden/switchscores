<?php

namespace App\Http\Controllers\Api\Game;

use App\Services\ServiceContainer;

use App\Services\Game\TitleMatch as ServiceTitleMatch;

class TitleMatch
{
    public function getByTitle()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $serviceGame = $serviceContainer->getGameService();

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
