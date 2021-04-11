<?php

namespace App\Http\Controllers\Staff\Games;

use Illuminate\Support\Facades\Validator;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Traits\SwitchServices;
use App\Traits\AuthUser;
use App\Traits\StaffView;

use App\Domain\Game\Repository as GameRepository;
use App\Domain\GameTag\Repository as GameTagRepository;
use App\Domain\Tag\Repository as TagRepository;
use App\Domain\TagCategory\Repository as TagCategoryRepository;

class GamesTagController extends Controller
{
    use SwitchServices;
    use AuthUser;
    use StaffView;

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
    ];

    protected $repoGame;
    protected $repoGameTag;
    protected $repoTag;
    protected $repoTagCategory;

    public function __construct(
        GameRepository $repoGame,
        GameTagRepository $repoGameTag,
        TagRepository $repoTag,
        TagCategoryRepository $repoTagCategory
    )
    {
        $this->repoGame = $repoGame;
        $this->repoGameTag = $repoGameTag;
        $this->repoTag = $repoTag;
        $this->repoTagCategory = $repoTagCategory;
    }

    public function edit($gameId)
    {
        $request = request();

        $gameData = $this->repoGame->find($gameId);
        if (!$gameData) abort(404);

        $bindings = $this->getBindingsGamesDetailSubpage('Editing tags for game: '.$gameData->title, $gameId);

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            // Delete existing tags for this game
            $this->repoGameTag->deleteAllForGame($gameId);

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