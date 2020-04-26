<?php

namespace App\Http\Controllers\Staff\Games;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;

class FindController extends Controller
{
    use SwitchServices;

    public function show()
    {
        $bindings = [];

        $pageTitle = 'Find a game';

        if (request()->isMethod('post')) {

            $gameId = request()->game_id;

            if ($gameId) {

                $game = $this->getServiceGame()->find($gameId);
                if (!$game) abort(404);

                return redirect(route('staff.games.detail', ['gameId' => $gameId]));

            }

        }

        $bindings['GamesList'] = $this->getServiceGame()->getAll();

        $bindings['TopTitle'] = $pageTitle.' - Staff';
        $bindings['PageTitle'] = $pageTitle;

        return view('staff.games.find.show', $bindings);
    }
}
