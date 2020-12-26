<?php

namespace App\Http\Controllers\Staff\Games;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;
use App\Traits\StaffView;

class FindController extends Controller
{
    use SwitchServices;
    use StaffView;

    public function show()
    {
        $bindings = $this->getBindingsGamesSubpage('Find a game');

        if (request()->isMethod('post')) {

            $gameId = request()->game_id;

            if ($gameId) {

                $game = $this->getServiceGame()->find($gameId);
                if (!$game) abort(404);

                return redirect(route('staff.games.detail', ['gameId' => $gameId]));

            }

        }

        $bindings['GamesList'] = $this->getServiceGame()->getAll();

        return view('staff.games.find.show', $bindings);
    }
}
