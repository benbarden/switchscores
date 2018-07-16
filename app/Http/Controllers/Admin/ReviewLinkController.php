<?php

namespace App\Http\Controllers\Admin;

use App\Events\ReviewLinkCreated;
use Illuminate\Http\Request;
use App\Game;
use App\ReviewLink;

class ReviewLinkController extends \App\Http\Controllers\BaseController
{
    /**
     * @var array
     */
    private $validationRules = [
        'game_id' => 'required|exists:games,id',
        'site_id' => 'required|exists:review_sites,id',
        'url' => 'required',
        'rating_original' => 'required'
    ];

    /**
     * @var \App\Services\ReviewLinkService
     */
    private $serviceClass;

    public function __construct()
    {
        $this->serviceClass = resolve('Services\ReviewLinkService');
        parent::__construct();
    }

    public function showList()
    {
        $serviceReviewLink = $this->serviceContainer->getReviewLinkService();
        $serviceReviewSite = $this->serviceContainer->getReviewSiteService();

        $bindings = [];

        $siteId = request()->siteId;

        $reviewSites = $serviceReviewSite->getAll();

        $bindings['TopTitle'] = 'Admin - Reviews - Links';
        $bindings['PanelTitle'] = 'Reviews: Links';

        $jsInitialSort = "[ 3, 'desc']";

        if (!$siteId) {
            $bindings['ActiveSiteId'] = '';
            $reviewLinks = $serviceReviewLink->getAll();
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
        $regionCode = \Request::get('regionCode');

        $request = request();

        $gameService = $this->serviceContainer->getGameService();
        $reviewSiteService = $this->serviceContainer->getReviewSiteService();
        $reviewStatsService = $this->serviceContainer->getReviewStatsService();

        if ($request->isMethod('post')) {

            $this->validate($request, $this->validationRules);

            $siteId = $request->site_id;
            $ratingOriginal = $request->rating_original;

            $reviewSite = $reviewSiteService->find($siteId);

            $ratingNormalised = $this->serviceClass->getNormalisedRating($ratingOriginal, $reviewSite);

            $reviewLink = $this->serviceClass->create(
                $request->game_id, $siteId, $request->url, $ratingOriginal, $ratingNormalised,
                $request->review_date, ReviewLink::TYPE_MANUAL
            );

            // Update game review stats
            $game = $gameService->find($request->game_id);
            $reviewStatsService->updateGameReviewStats($game);

            // Trigger event
            event(new ReviewLinkCreated($reviewLink));

            // All done; send us back
            return redirect(route('admin.reviews.link.list').'?siteId='.$siteId);

        }

        $bindings = array();

        $bindings['TopTitle'] = 'Admin - Reviews - Add link';
        $bindings['PanelTitle'] = 'Add review link';
        $bindings['FormMode'] = 'add';

        $bindings['GamesList'] = $gameService->getAll($regionCode);

        $bindings['ReviewSites'] = $reviewSiteService->getAll();

        return view('admin.reviews.link.add', $bindings);
    }

    public function edit($linkId)
    {
        $regionCode = \Request::get('regionCode');

        $reviewLinkData = $this->serviceClass->find($linkId);
        if (!$reviewLinkData) abort(404);

        $gameService = $this->serviceContainer->getGameService();
        $reviewSiteService = $this->serviceContainer->getReviewSiteService();
        $reviewStatsService = $this->serviceContainer->getReviewStatsService();

        $request = request();
        $bindings = array();

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $this->validate($request, $this->validationRules);

            $siteId = $request->site_id;
            $ratingOriginal = $request->rating_original;

            $reviewSite = $reviewSiteService->find($siteId);

            $ratingNormalised = $this->serviceClass->getNormalisedRating($ratingOriginal, $reviewSite);

            $this->serviceClass->edit(
                $reviewLinkData,
                $request->game_id, $siteId, $request->url, $ratingOriginal, $ratingNormalised,
                $request->review_date
            );

            // Update game review stats
            $game = $gameService->find($request->game_id);
            $reviewStatsService->updateGameReviewStats($game);

            // Update ranks
            \Artisan::call('UpdateGameRanks');

            // All done; send us back
            return redirect(route('admin.reviews.link.list').'?siteId='.$siteId);

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['TopTitle'] = 'Admin - Reviews - Edit link';
        $bindings['PanelTitle'] = 'Edit review link';
        $bindings['ReviewLinkData'] = $reviewLinkData;
        $bindings['LinkId'] = $linkId;

        $bindings['GamesList'] = $gameService->getAll($regionCode);

        $bindings['ReviewSites'] = $reviewSiteService->getAll();

        return view('admin.reviews.link.edit', $bindings);
    }
}
