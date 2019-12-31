<?php


namespace App\Http\Controllers\Staff\Games;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Traits\SiteRequestData;
use App\Traits\WosServices;

use App\Construction\GameImportRule\WikipediaDirector;
use App\Construction\GameImportRule\WikipediaBuilder;

class ImportRuleWikipediaController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    use WosServices;
    use SiteRequestData;

    /**
     * @var array
     */
    private $validationRules = [
    ];

    public function edit($gameId)
    {
        $serviceImportRuleWikipedia = $this->getServiceGameImportRuleWikipedia();

        $gameImportRuleWikipedia = $serviceImportRuleWikipedia->getByGameId($gameId);

        $request = request();
        $bindings = [];

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $this->validate($request, $this->validationRules);

            // Update the DB
            $importRuleDirector = new WikipediaDirector();
            $importRuleBuilder = new WikipediaBuilder();
            $importRuleDirector->setBuilder($importRuleBuilder);
            if ($gameImportRuleWikipedia) {
                $importRuleDirector->buildExisting($gameImportRuleWikipedia, $request->post());
            } else {
                $importRuleBuilder->setGameId($gameId);
                $importRuleDirector->buildNew($request->post());
            }
            $importRule = $importRuleBuilder->getGameImportRule();
            $importRule->save();

            // All done; send us back
            return redirect(route('staff.games.detail', ['gameId' => $gameId]));

        } elseif ($gameImportRuleWikipedia) {

            $bindings['FormMode'] = 'edit';

        } else {

            $bindings['FormMode'] = 'add';

        }

        $bindings['TopTitle'] = 'Import rules: Wikipedia - Edit';
        $bindings['PageTitle'] = 'Import rules: Wikipedia';
        $bindings['GameImportRuleWikipedia'] = $gameImportRuleWikipedia;
        $bindings['GameId'] = $gameId;

        return view('staff.games.import-rule-wikipedia.edit', $bindings);
    }
}