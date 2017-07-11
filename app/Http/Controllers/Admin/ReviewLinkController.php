<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

class ReviewLinkController extends \App\Http\Controllers\BaseController
{
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
        $bindings = array();

        $bindings['TopTitle'] = 'Admin - Reviews - Links';

        $reviewLinks = $this->serviceClass->getAll();

        $bindings['ReviewLinks'] = $reviewLinks;

        return view('admin.reviews.link.list', $bindings);
    }

    public function add()
    {
        $request = request();

        $gameService = resolve('Services\GameService');
        $reviewSiteService = resolve('Services\ReviewSiteService');

        if ($request->isMethod('post')) {

            $this->validate($request, [
                'game_id' => 'required|exists:games,id',
                'site_id' => 'required|exists:review_sites,id',
                'url' => 'required',
                'rating_original' => 'required'
            ]);

            $gameId = $request->game_id;
            $siteId = $request->site_id;
            $url = $request->url;
            $ratingOriginal = $request->rating_original;

            $normalisedScaleLimit = 10;

            $reviewSite = $reviewSiteService->find($siteId);
            if ($reviewSite->rating_scale != $normalisedScaleLimit) {
                $scaleMultiple = $normalisedScaleLimit / $reviewSite->rating_scale;
                $ratingNormalised = round($ratingOriginal * $scaleMultiple, 2);
            } else {
                $ratingNormalised = $ratingOriginal;
            }

            $this->serviceClass->create(
                $gameId, $siteId, $url, $ratingOriginal, $ratingNormalised
            );

            return redirect(route('admin.reviews.link.list'));

        }

        $bindings = array();

        $bindings['TopTitle'] = 'Admin - Add review link';

        $bindings['GamesList'] = $gameService->getAll();

        $bindings['ReviewSites'] = $reviewSiteService->getAll();

        return view('admin.reviews.link.add', $bindings);
    }
}
