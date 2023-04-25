<?php

namespace App\Http\Controllers\PublicSite\Games;

use App\Domain\Game\Repository as GameRepository;
use Illuminate\Routing\Controller as Controller;

class RandomController extends Controller
{
    protected $repoGame;

    public function __construct(
        GameRepository $repoGame
    )
    {
        $this->repoGame = $repoGame;
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
