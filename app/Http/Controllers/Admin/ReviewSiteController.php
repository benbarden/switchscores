<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Services\ServiceContainer;

class ReviewSiteController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
        'name' => 'required|max:50',
        'url' => 'required',
        'feed_url' => 'max:255',
        'link_title' => 'required|max:100',
    ];

    public function showList()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $serviceReviewSite = $serviceContainer->getReviewSiteService();

        $bindings = [];

        $bindings['TopTitle'] = 'Admin - Reviews - Sites';
        $bindings['PanelTitle'] = 'Reviews: Sites';

        $reviewSites = $serviceReviewSite->getAll();

        $bindings['ReviewSites'] = $reviewSites;

        return view('admin.reviews.site.list', $bindings);
    }

    public function add()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $serviceReviewSite = $serviceContainer->getReviewSiteService();

        $request = request();

        if ($request->isMethod('post')) {

            $this->validate($request, $this->validationRules);

            $isActive = $request->active == 'on' ? 'Y' : 'N';

            if (isset($request->rating_scale)) {
                $ratingScale = $request->rating_scale;
            } else {
                $ratingScale = 10;
            }

            $serviceReviewSite->create(
                $request->name, $request->link_title, $request->url, $request->feed_url,
                $isActive, $ratingScale
            );

            return redirect(route('admin.reviews.site.list'));

        }

        $bindings = [];

        $bindings['TopTitle'] = 'Admin - Reviews - Sites - Add site';
        $bindings['PanelTitle'] = 'Add site';
        $bindings['FormMode'] = 'add';

        return view('admin.reviews.site.add', $bindings);
    }

    public function edit($siteId)
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $serviceReviewSite = $serviceContainer->getReviewSiteService();

        $reviewSiteData = $serviceReviewSite->find($siteId);
        if (!$reviewSiteData) abort(404);

        $request = request();

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $this->validate($request, $this->validationRules);

            $isActive = $request->active == 'on' ? 'Y' : 'N';

            if (isset($request->rating_scale)) {
                $ratingScale = $request->rating_scale;
            } else {
                $ratingScale = 10;
            }

            $serviceReviewSite->edit(
                $reviewSiteData,
                $request->name, $request->link_title, $request->url, $request->feed_url,
                $isActive, $ratingScale
            );

            return redirect(route('admin.reviews.site.list'));

        }

        $bindings = [];

        $bindings['TopTitle'] = 'Admin - Reviews - Sites - Edit site';
        $bindings['PanelTitle'] = 'Edit site';
        $bindings['ReviewSiteData'] = $reviewSiteData;
        $bindings['SiteId'] = $siteId;

        $bindings['FormMode'] = 'edit';

        return view('admin.reviews.site.edit', $bindings);
    }
}
