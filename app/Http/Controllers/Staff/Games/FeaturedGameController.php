<?php


namespace App\Http\Controllers\Staff\Games;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;

use App\Domain\FeaturedGame\Repository as FeaturedGameRepository;

use App\Traits\SwitchServices;

class FeaturedGameController extends Controller
{
    use SwitchServices;

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
        'game_id' => 'exists:games,id',
    ];

    public function __construct(
        private FeaturedGameRepository $repoFeaturedGames
    )
    {
    }

    public function add()
    {
        $pageTitle = 'Add featured game';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesFeaturedGamesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $request = request();

        if ($request->isMethod('post')) {

            $this->validate($request, $this->validationRules);

            $this->repoFeaturedGames->create(
                $request->user_id,
                $request->game_id,
                $request->featured_date,
                $request->featured_type,
                $request->status
            );

            return redirect(route('staff.games.featured-games.list'));

        }

        $bindings['FormMode'] = 'add';

        $bindings['GamesList'] = $this->getServiceGame()->getAll();

        return view('staff.games.featured-games.add', $bindings);
    }

    public function edit($featuredGameId)
    {
        $pageTitle = 'Edit featured game';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesFeaturedGamesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $featuredGame = $this->repoFeaturedGames->find($featuredGameId);
        if (!$featuredGame) abort(404);

        $request = request();

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $this->validate($request, $this->validationRules);

            $this->repoFeaturedGames->edit(
                $featuredGame,
                $request->game_id,
                $request->featured_date,
                $request->featured_type,
                $request->status
            );

            return redirect(route('staff.games.featured-games.list'));

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['FeaturedGameData'] = $featuredGame;
        $bindings['FeaturedGameId'] = $featuredGameId;

        $bindings['GamesList'] = $this->getServiceGame()->getAll();

        return view('staff.games.featured-games.edit', $bindings);
    }

    public function showList()
    {
        $pageTitle = 'Featured games';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['FeaturedGamesList'] = $this->repoFeaturedGames->getAll();

        return view('staff.games.featured-games.list', $bindings);
    }

    public function acceptItem()
    {
        $request = request();
        $itemId = $request->itemId;
        $featuredGame = $this->repoFeaturedGames->find($itemId);
        if (!$featuredGame) {
            return response()->json(['error' => 'Record not found: '.$itemId], 400);
        }

        $this->repoFeaturedGames->acceptItem($featuredGame);

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }

    public function rejectItem()
    {
        $request = request();
        $itemId = $request->itemId;
        $featuredGame = $this->repoFeaturedGames->find($itemId);
        if (!$featuredGame) {
            return response()->json(['error' => 'Record not found: '.$itemId], 400);
        }

        $this->repoFeaturedGames->rejectItem($featuredGame);

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }

    public function archiveItem()
    {
        $request = request();
        $itemId = $request->itemId;
        $featuredGame = $this->repoFeaturedGames->find($itemId);
        if (!$featuredGame) {
            return response()->json(['error' => 'Record not found: '.$itemId], 400);
        }

        $this->repoFeaturedGames->archiveItem($featuredGame);

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }
}