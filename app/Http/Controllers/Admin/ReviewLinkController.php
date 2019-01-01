<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Services\ServiceContainer;

use App\ReviewLink;

use App\Events\ReviewLinkCreated;

class ReviewLinkController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
        'game_id' => 'required|exists:games,id',
        'site_id' => 'required|exists:review_sites,id',
        'url' => 'required',
        'rating_original' => 'required'
    ];

    public function showList()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $serviceReviewLink = $serviceContainer->getReviewLinkService();
        $serviceReviewSite = $serviceContainer->getReviewSiteService();

        $bindings = [];

        $siteId = request()->siteId;

        $reviewSites = $serviceReviewSite->getAll();

        $bindings['TopTitle'] = 'Admin - Reviews - Links';
        $bindings['PanelTitle'] = 'Reviews: Links';

        $jsInitialSort = "[ 3, 'desc']";

        if (!$siteId) {
            $bindings['ActiveSiteId'] = '';
            $tableLimit = 250;
            $reviewLinks = $serviceReviewLink->getAll($tableLimit);
            $bindings['TableLimit'] = $tableLimit;
        } else {
            $bindings['ActiveSiteId'] = $siteId;
            $reviewLinks = $serviceReviewLink->getAllBySite($siteId);
        }

        $bindings['ReviewLinks'] = $reviewLinks;
        $bindings['ReviewSites'] = $reviewSites;
        $bindings['jsInitialSort'] = $jsInitialSort;

        return view('admin.reviews.link.list', $bindings);
    }

    public function add()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $regionCode = \Request::get('regionCode');

        $request = request();

        $serviceGame = $serviceContainer->getGameService();
        $serviceReviewStats = $serviceContainer->getReviewStatsService();
        $serviceReviewLink = $serviceContainer->getReviewLinkService();
        $serviceReviewSite = $serviceContainer->getReviewSiteService();

        if ($request->isMethod('post')) {

            $this->validate($request, $this->validationRules);

            $siteId = $request->site_id;
            $ratingOriginal = $request->rating_original;

            $reviewSite = $serviceReviewSite->find($siteId);

            $ratingNormalised = $serviceReviewLink->getNormalisedRating($ratingOriginal, $reviewSite);

            $reviewLink = $serviceReviewLink->create(
                $request->game_id, $siteId, $request->url, $ratingOriginal, $ratingNormalised,
                $request->review_date, ReviewLink::TYPE_MANUAL
            );

            // Update game review stats
            $game = $serviceGame->find($request->game_id);
            $serviceReviewStats->updateGameReviewStats($game);

            // Trigger event
            event(new ReviewLinkCreated($reviewLink));

            // All done; send us back
            return redirect(route('admin.reviews.link.list').'?siteId='.$siteId);

        }

        $bindings = [];

        $bindings['TopTitle'] = 'Admin - Reviews - Add link';
        $bindings['PanelTitle'] = 'Add review link';
        $bindings['FormMode'] = 'add';

        $bindings['GamesList'] = $serviceGame->getAll($regionCode);

        $bindings['ReviewSites'] = $serviceReviewSite->getAll();

        return view('admin.reviews.link.add', $bindings);
    }

    public function edit($linkId)
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $regionCode = \Request::get('regionCode');

        $serviceGame = $serviceContainer->getGameService();
        $serviceReviewStats = $serviceContainer->getReviewStatsService();
        $serviceReviewLink = $serviceContainer->getReviewLinkService();
        $serviceReviewSite = $serviceContainer->getReviewSiteService();

        $reviewLinkData = $serviceReviewLink->find($linkId);
        if (!$reviewLinkData) abort(404);

        $request = request();

        $bindings = [];

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $this->validate($request, $this->validationRules);

            $siteId = $request->site_id;
            $ratingOriginal = $request->rating_original;

            $reviewSite = $serviceReviewSite->find($siteId);

            $ratingNormalised = $serviceReviewLink->getNormalisedRating($ratingOriginal, $reviewSite);

            $serviceReviewLink->edit(
                $reviewLinkData,
                $request->game_id, $siteId, $request->url, $ratingOriginal, $ratingNormalised,
                $request->review_date
            );

            // Update game review stats
            $game = $serviceGame->find($request->game_id);
            $serviceReviewStats->updateGameReviewStats($game);

            // Update ranks
            //\Artisan::call('UpdateGameRanks');

            // All done; send us back
            return redirect(route('admin.reviews.link.list').'?siteId='.$siteId);

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['TopTitle'] = 'Admin - Reviews - Edit link';
        $bindings['PanelTitle'] = 'Edit review link';
        $bindings['ReviewLinkData'] = $reviewLinkData;
        $bindings['LinkId'] = $linkId;

        $bindings['GamesList'] = $serviceGame->getAll($regionCode);

        $bindings['ReviewSites'] = $serviceReviewSite->getAll();

        return view('admin.reviews.link.edit', $bindings);
    }
}
