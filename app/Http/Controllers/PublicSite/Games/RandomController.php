<?php

namespace App\Http\Controllers\PublicSite\Games;

use App\Domain\Game\Repository as GameRepository;
use App\Models\Console;
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

        if ($game->console_id === Console::ID_SWITCH_2) {
            $redirUrl = route('game.show.switch2', [
                'id' => $game->id,
                'linkTitle' => $game->link_title
            ]);
        } else {
            $redirUrl = sprintf('/games/%s/%s', $game->id, $game->link_title);
        }

        return redirect($redirUrl);
    }

}
