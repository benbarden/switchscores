<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

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

    public function showList($report = null)
    {
        $bindings = array();

        $bindings['TopTitle'] = 'Admin - Reviews - Links';
        $bindings['PanelTitle'] = 'Reviews: Links';

        $jsInitialSort = "[ 3, 'desc']";

        if ($report == null) {
            $bindings['ActiveNav'] = 'all';
            $reviewLinks = $this->serviceClass->getAll();
        } else {
            $bindings['ActiveNav'] = $report;
            switch ($report) {
                case 'no-date':
                    $reviewLinks = $this->serviceClass->getAllWithoutDate();
                    break;
                default:
                    abort(404);
            }
        }

        $bindings['ReviewLinks'] = $reviewLinks;
        $bindings['jsInitialSort'] = $jsInitialSort;

        return view('admin.reviews.link.list', $bindings);
    }

    public function add()
    {
        $request = request();

        $gameService = resolve('Services\GameService');
        $reviewSiteService = resolve('Services\ReviewSiteService');

        if ($request->isMethod('post')) {

            $this->validate($request, $this->validationRules);

            $siteId = $request->site_id;
            $ratingOriginal = $request->rating_original;

            $reviewSite = $reviewSiteService->find($siteId);

            $ratingNormalised = $this->serviceClass->getNormalisedRating($ratingOriginal, $reviewSite);

            $this->serviceClass->create(
                $request->game_id, $siteId, $request->url, $ratingOriginal, $ratingNormalised,
                $request->review_date
            );

            return redirect(route('admin.reviews.link.list'));

        }

        $bindings = array();

        $bindings['TopTitle'] = 'Admin - Reviews - Add link';
        $bindings['PanelTitle'] = 'Add review link';
        $bindings['FormMode'] = 'add';

        $bindings['GamesList'] = $gameService->getAll();

        $bindings['ReviewSites'] = $reviewSiteService->getAll();

        return view('admin.reviews.link.add', $bindings);
    }

    public function edit($linkId)
    {
        $reviewLinkData = $this->serviceClass->find($linkId);
        if (!$reviewLinkData) abort(404);

        $gameService = resolve('Services\GameService');
        $reviewSiteService = resolve('Services\ReviewSiteService');

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

            return redirect(route('admin.reviews.link.list'));

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['TopTitle'] = 'Admin - Reviews - Edit link';
        $bindings['PanelTitle'] = 'Edit review link';
        $bindings['ReviewLinkData'] = $reviewLinkData;
        $bindings['LinkId'] = $linkId;

        $bindings['GamesList'] = $gameService->getAll();

        $bindings['ReviewSites'] = $reviewSiteService->getAll();

        return view('admin.reviews.link.edit', $bindings);
    }
}
