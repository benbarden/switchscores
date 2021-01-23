<?php

namespace App\Http\Controllers\User;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Domain\FeaturedGame\Repository as FeaturedGameRepository;

use App\Traits\SwitchServices;
use App\Traits\AuthUser;
use App\Traits\MemberView;

class FeaturedGameController extends Controller
{
    use SwitchServices;
    use AuthUser;
    use MemberView;

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
        'game_id' => 'exists:games,id',
        'user_id' => 'exists:users,id',
        'featured_type' => 'required'
    ];

    protected $repoFeaturedGame;

    public function __construct(FeaturedGameRepository $featuredGame)
    {
        $this->repoFeaturedGame = $featuredGame;
    }

    public function add($gameId)
    {
        $bindings = $this->getBindingsFeaturedGamesSubpage('Add featured game');

        $gameData = $this->getServiceGame()->find($gameId);
        if (!$gameData) abort(404);

        // Don't allow duplicates
        $featuredGameIdList = $this->repoFeaturedGame->getAllGameIds();
        if ($featuredGameIdList->contains($gameId)) {
            return redirect(route('user.index'));
        }

        $request = request();

        if ($request->isMethod('post')) {

            $this->validate($request, $this->validationRules);

            $userId = $this->getAuthId();
            $featuredType = $request->featured_type;
            $this->repoFeaturedGame->createFromUserSubmission($userId, $gameId, $featuredType);

            return redirect(route('user.index'));

        }

        $bindings['FormMode'] = 'add';
        $bindings['GameId'] = $gameId;
        $bindings['GameData'] = $gameData;

        return view('user.featured-games.add', $bindings);
    }
}
