<?php

namespace App\Http\Controllers\Staff\Games;

use Illuminate\Support\Facades\Validator;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;

use App\Domain\Game\Repository as GameRepository;
use App\Domain\GameTag\Repository as GameTagRepository;
use App\Domain\Tag\Repository as TagRepository;
use App\Domain\TagCategory\Repository as TagCategoryRepository;

class GamesTagController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
    ];

    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private GameRepository $repoGame,
        private GameTagRepository $repoGameTag,
        private TagRepository $repoTag,
        private TagCategoryRepository $repoTagCategory
    )
    {
    }

    public function edit($gameId)
    {
        $request = request();

        $gameData = $this->repoGame->find($gameId);
        if (!$gameData) abort(404);

        $pageTitle = 'Game tags: '.$gameData->title;
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::gamesDetailSubpage($pageTitle, $gameData))->bindings;

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            // Delete existing tags for this game
            $this->repoGameTag->deleteByGameId($gameId);

            $tagItems = $request->tag_item;
            if ($tagItems) {
                foreach ($tagItems as $tagItem) {
                    $tagData = $this->repoTag->find($tagItem);
                    if ($tagData) {
                        $this->repoGameTag->create($gameId, $tagData->id);
                    }
                }
            }

            //$this->validate($request, $this->validationRules);

            // Done
            return redirect('/staff/games/detail/'.$gameId.'?lastaction=edit&lastgameid='.$gameId);

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['GameData'] = $gameData;
        $bindings['GameId'] = $gameId;

        $bindings['GameTagList'] = $this->repoGameTag->getGameTags($gameId);
        $bindings['TagCategoryList'] = $this->repoTagCategory->getAll();

        return view('staff.games.tag.edit', $bindings);
    }

}