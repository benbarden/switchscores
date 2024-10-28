<?php

namespace App\Http\Controllers\User;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Domain\FeaturedGame\Repository as FeaturedGameRepository;
use App\Domain\Game\Repository as GameRepository;

class FeaturedGameController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
        'game_id' => 'exists:games,id',
        'user_id' => 'exists:users,id',
        'featured_type' => 'required'
    ];

    public function __construct(
        private FeaturedGameRepository $repoFeaturedGame,
        private GameRepository $repoGame
    )
    {
    }

    public function add($gameId)
    {
        $pageTitle = 'Add featured game';
        $breadcrumbs = resolve('View/Breadcrumbs/Member')->topLevelPage($pageTitle);
        $bindings = resolve('View/Bindings/Member')->setBreadcrumbs($breadcrumbs)->generateMember($pageTitle);

        $gameData = $this->repoGame->find($gameId);
        if (!$gameData) abort(404);

        // Don't allow duplicates
        $featuredGameIdList = $this->repoFeaturedGame->getAllGameIds();
        if ($featuredGameIdList->contains($gameId)) {
            return redirect(route('user.index'));
        }

        $request = request();

        if ($request->isMethod('post')) {

            $this->validate($request, $this->validationRules);

            $currentUser = resolve('User/Repository')->currentUser();
            $userId = $currentUser->id;
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
