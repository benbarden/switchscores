<?php

namespace App\Http\Controllers\Staff\Games;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Validator;

use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;

use App\Domain\AffiliateCodes\Amazon;
use App\Domain\AffiliateCodes\AsinExtractor;
use App\Domain\AffiliateCodes\TopRatedGaps;
use App\Domain\Game\Repository as GameRepository;
use App\Domain\Game\Repository\GameAffiliateRepository;

use App\Enums\AmazonAffiliateStatus;

use App\Models\Console;
use App\Models\Game;

class AffiliatesController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private GameRepository $repoGame,
        private GameAffiliateRepository $repoGameAffiliate,
        private AsinExtractor $asinExtractor,
        private Amazon $affiliateAmazon,
        private TopRatedGaps $topRatedGaps,
    )
    {
    }

    /**
     * What's wrong with a pasted ASIN or URL, or null if it can be saved for this region.
     */
    private function asinProblem(mixed $value, string $region): ?string
    {
        if ($this->asinExtractor->isWrongStore($value, $region)) {
            return 'That URL is not from Amazon '.strtoupper($region).'. Check you are on the right store and the right game.';
        }

        if ($this->asinExtractor->extract($value) === null) {
            return 'Enter an ASIN or a full Amazon product URL.';
        }

        return null;
    }

    private function asinRule(string $region): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) use ($region) {
            $problem = $this->asinProblem($value, $region);
            if ($problem !== null) {
                $fail($problem);
            }
        };
    }

    public function topRatedGaps(string $region, Console $console, string $scope)
    {
        if (!in_array($region, TopRatedGaps::REGIONS, true) || !$this->topRatedGaps->isValidScope($scope)) {
            abort(404);
        }

        $regionName = 'Amazon '.strtoupper($region);
        $pageTitle = 'Top rated gaps - '.$regionName.': '.$console->name.' '.$scope;
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::gamesAffiliatesSubpage($pageTitle))->bindings;

        $rows = [];
        foreach ($this->topRatedGaps->games($region, $console->id, $scope) as $game) {
            $rows[] = [
                'game' => $game,
                'searchUrl' => $this->affiliateAmazon->staffSearchUrl($game, $region),
            ];
        }

        $pagePath = $this->topRatedGaps->pagePath($console, $scope);

        $bindings['Region'] = $region;
        $bindings['RegionName'] = $regionName;
        $bindings['GapRows'] = $rows;
        $bindings['PagePath'] = $pagePath;
        $bindings['PageStats'] = $this->topRatedGaps->pageStats($pagePath);

        return view('staff.games.affiliates.top-rated-gaps', $bindings);
    }

    /**
     * Saves one region from a Top rated gaps row: an ASIN or pasted URL, or No product.
     * Refuses to replace a link, so a stale tab can't overwrite one.
     */
    public function quickSave(Request $request, $gameId)
    {
        $game = $this->repoGame->findWithoutCache($gameId);
        if (!$game) {
            return response()->json(['error' => 'Game not found.'], 404);
        }

        $region = $request->input('region');
        if (!in_array($region, TopRatedGaps::REGIONS, true)) {
            return response()->json(['error' => 'Unknown region.'], 422);
        }

        if ($this->repoGameAffiliate->hasDirectLink($game, $region)) {
            return response()->json(['error' => 'This game already has a link. Use the edit page to change it.'], 409);
        }

        if ($request->boolean('no_product')) {
            $this->repoGameAffiliate->markRegionNoProduct($game, $region);
            $status = AmazonAffiliateStatus::NO_PRODUCT;
            $asin = null;
        } else {
            $problem = $this->asinProblem($request->input('asin'), $region);
            if ($problem !== null) {
                return response()->json(['error' => $problem], 422);
            }
            $asin = $this->asinExtractor->extract($request->input('asin'));
            $this->repoGameAffiliate->saveRegionAsin($game, $region, $asin);
            $status = AmazonAffiliateStatus::LINKED;
        }

        $this->repoGame->clearCacheCoreData($gameId);

        return response()->json([
            'region' => $region,
            'asin' => $asin,
            'statusLabel' => AmazonAffiliateStatus::options()[$status->value],
        ]);
    }

    public function edit(Request $request, $gameId)
    {
        $gameData = $this->repoGame->find($gameId);
        if (!$gameData) abort(404);

        $pageTitle = 'Edit game affiliates: '.$gameData->title;
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::gamesDetailSubpage($pageTitle, $gameData))->bindings;

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';
            //$this->validate($request, $this->validationRules);

            $validator = Validator::make($request->all(), [
                'amazon_uk_asin' => ['nullable', $this->asinRule('uk')],
                'amazon_us_asin' => ['nullable', $this->asinRule('us')],
            ]);

            if ($validator->fails()) {
                return redirect(route('staff.games.editAffiliates', ['gameId' => $gameId]))
                    ->withErrors($validator)
                    ->withInput();
            }

            // Update fields
            $amazonUKASIN = $this->asinExtractor->extract($request->amazon_uk_asin);
            $amazonUKLink = $request->amazon_uk_link;
            $amazonUKStatus = $request->amazon_uk_status;
            $amazonUSASIN = $this->asinExtractor->extract($request->amazon_us_asin);
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
        $bindings['AmazonSearchUk'] = $this->affiliateAmazon->staffSearchUrl($gameData, 'uk');
        $bindings['AmazonSearchUs'] = $this->affiliateAmazon->staffSearchUrl($gameData, 'us');

        $bindings['AffiliateStatusList'] = AmazonAffiliateStatus::options();

        return view('staff.games.affiliates.edit', $bindings);
    }
}