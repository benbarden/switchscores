<?php

namespace App\Http\Controllers\Staff\Games;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Validator;

use App\Domain\Game\Repository as GameRepository;
use App\Domain\Game\Repository\GameAffiliateRepository;

use App\Enums\AmazonAffiliateStatus;

use App\Models\Game;

class AffiliatesController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
        'amazon_uk_asin' => 'max:12',
        'amazon_us_asin' => 'max:12',
    ];

    public function __construct(
        private GameRepository $repoGame,
        private GameAffiliateRepository $repoGameAffiliate,
    )
    {
    }

    public function edit(Request $request, $gameId)
    {
        $gameData = $this->repoGame->find($gameId);
        if (!$gameData) abort(404);

        $pageTitle = 'Edit game affiliates: '.$gameData->title;
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesDetailSubpage($pageTitle, $gameData);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';
            //$this->validate($request, $this->validationRules);

            $validator = Validator::make($request->all(), $this->validationRules);

            if ($validator->fails()) {
                return redirect(route('staff.games.editAffiliates', ['gameId' => $gameId]))
                    ->withErrors($validator)
                    ->withInput();
            }

            // Update fields
            $amazonUKASIN = $request->amazon_uk_asin;
            $amazonUKLink = $request->amazon_uk_link;
            $amazonUKStatus = $request->amazon_uk_status;
            $amazonUSASIN = $request->amazon_us_asin;
            $amazonUSLink = $request->amazon_us_link;
            $amazonUSStatus = $request->amazon_us_status;
            $this->repoGameAffiliate->updateAffiliateData(
                $gameData, $amazonUKASIN, $amazonUKLink, $amazonUKStatus, $amazonUSASIN, $amazonUSLink, $amazonUSStatus
            );

            // Clear cache
            $this->repoGame->clearCacheCoreData($gameId);

            // Done
            return redirect('/staff/games/detail/'.$gameId.'?lastaction=edit&lastgameid='.$gameId);

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['GameData'] = $gameData;
        $bindings['GameId'] = $gameId;

        $bindings['AffiliateStatusList'] = AmazonAffiliateStatus::options();

        return view('staff.games.affiliates.edit', $bindings);
    }
}