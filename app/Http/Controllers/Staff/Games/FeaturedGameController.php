<?php


namespace App\Http\Controllers\Staff\Games;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;

use App\Domain\FeaturedGame\Repository as FeaturedGameRepository;

class FeaturedGameController extends Controller
{
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