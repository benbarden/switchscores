<?php

namespace App\Http\Controllers\PublicSite\Games;

use App\Domain\Game\Repository as GameRepository;
use Illuminate\Routing\Controller as Controller;

class RandomController extends Controller
{
    public function __construct(
        private GameRepository $repoGame
    )
    {
    }

    /**
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function getRandom()
    {
        $game = $this->repoGame->randomGame();

        $gameId = $game->id;
        $gameLinkTitle = $game->link_title;

        $redirUrl = sprintf('/games/%s/%s', $gameId, $gameLinkTitle);
        return redirect($redirUrl);
    }

}
