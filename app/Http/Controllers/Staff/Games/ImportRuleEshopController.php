<?php


namespace App\Http\Controllers\Staff\Games;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Construction\GameImportRule\EshopDirector;
use App\Construction\GameImportRule\EshopBuilder;

use App\Domain\Game\Repository as GameRepository;

use App\Traits\SwitchServices;

class ImportRuleEshopController extends Controller
{
    use SwitchServices;

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
    ];

    public function __construct(
        private GameRepository $repoGame
    )
    {
    }

    public function edit($gameId)
    {
        $game = $this->repoGame->find($gameId);
        if (!$game) abort(404);

        $pageTitle = 'Import rules: eShop - Edit';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesDetailSubpage($pageTitle, $game);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $gameImportRuleEshop = $this->getServiceGameImportRuleEshop()->getByGameId($gameId);

        $request = request();

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $this->validate($request, $this->validationRules);

            // Update the DB
            $importRuleDirector = new EshopDirector();
            $importRuleBuilder = new EshopBuilder();
            $importRuleDirector->setBuilder($importRuleBuilder);
            if ($gameImportRuleEshop) {
                $importRuleDirector->buildExisting($gameImportRuleEshop, $request->post());
            } else {
                $importRuleBuilder->setGameId($gameId);
                $importRuleDirector->buildNew($request->post());
            }
            $importRule = $importRuleBuilder->getGameImportRule();
            $importRule->save();

            // All done; send us back
            return redirect(route('staff.games.detail', ['gameId' => $gameId]));

        } elseif ($gameImportRuleEshop) {

            $bindings['FormMode'] = 'edit';

        } else {

            $bindings['FormMode'] = 'add';

        }

        $bindings['GameImportRuleEshop'] = $gameImportRuleEshop;
        $bindings['GameId'] = $gameId;

        return view('staff.games.import-rule-eshop.edit', $bindings);
    }
}