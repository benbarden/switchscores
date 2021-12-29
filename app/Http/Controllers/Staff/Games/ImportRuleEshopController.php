<?php


namespace App\Http\Controllers\Staff\Games;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Construction\GameImportRule\Director;
use App\Construction\GameImportRule\Builder;

use App\Traits\SwitchServices;
use App\Traits\StaffView;

class ImportRuleEshopController extends Controller
{
    use SwitchServices;
    use StaffView;

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
    ];

    public function edit($gameId)
    {
        $bindings = $this->getBindingsGamesDetailSubpage('Import rules: eShop - Edit', $gameId);

        $gameImportRuleEshop = $this->getServiceGameImportRuleEshop()->getByGameId($gameId);

        $request = request();

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $this->validate($request, $this->validationRules);

            // Update the DB
            $importRuleDirector = new Director();
            $importRuleBuilder = new Builder();
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